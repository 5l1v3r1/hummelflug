<?php

namespace Symfony\Component\Console\Command;

use Aws\Ec2\Ec2Client;
use Aws\Ec2\Exception\Ec2Exception;
use Aws\Result;
use Ssh\Authentication\PublicKeyFile;
use Ssh\Configuration;
use Ssh\Session;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{
    private $defaultCount = 4;
    private $defaultKeyPairName = 'hummelflug';
    private $defaultSecurityGroupName = 'hummelflug';

    private $count;
    private $keyPairName;
    private $securityGroupName;

    /**
     * @var Ec2Client
     */
    private $client;
    private $configuration;

    protected function configure()
    {
        $this
            ->setName('create')
            ->setDescription('Creates new bumblebees.')
            ->setHelp('Creates new bumblebees.')
            ->addOption(
                'count',
                '-c',
                InputOption::VALUE_REQUIRED,
                'How many bumblebees do you want?',
                $this->defaultCount
            )
            ->addOption(
                'keypair',
                '-k',
                InputOption::VALUE_REQUIRED,
                'Provide a keypair name, please!',
                $this->defaultKeyPairName
            )
            ->addOption(
                'groupname',
                '-g',
                InputOption::VALUE_REQUIRED,
                'Provide a security group name, please!',
                $this->defaultSecurityGroupName
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Setting up ' . $this->count . ' bumblebees</info>');

        $pids = [];

        for ($i = 1; $i <= $this->count; $i++) {
            $pid = pcntl_fork();

            if ($pid == -1) {
                throw new \Exception('Setting up bumblebee no ' . $i . ' failed');
            } else if ($pid) {
                $pids[$pid] = $i;
            } else {
                $this->setup($i, $input, $output);
                exit;
            }
        }

        while (!empty($pids)) {
            $pid = pcntl_wait($status);

            unset ($pids[$pid]);
        }
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->count = $input->getOption('count');
        $this->keyPairName = $input->getOption('keypair');
        $this->securityGroupName = $input->getOption('groupname');

        $this->configuration = parse_ini_file(__DIR__ . '/../config/config.ini', true);

        $this->client = new Ec2Client([
            'credentials' => [
                'key' => $this->configuration['credentials']['AWSAccessKeyId'],
                'secret' => $this->configuration['credentials']['AWSSecretKey'],
            ],
            'region' => $this->configuration['main']['region'],
            'version' => '2016-11-15',
        ]);

        if (!$this->client instanceof Ec2Client) {
            throw new \Exception('Could not create client.');
        }

        if (!file_exists(__DIR__ . '/../.ssh/' . $this->keyPairName)) {
            exec('ssh-keygen -q -f .ssh/hummelflug -N \'\'');

            try {
                $keyPair = $this->client->importKeyPair([
                    'KeyName' => $this->keyPairName,
                    'PublicKeyMaterial' => file_get_contents(
                        __DIR__ . '/../.ssh/' . $this->keyPairName . '.pub'
                    )
                ]);

                if (!$keyPair instanceof Result) {
                    throw new \Exception('Could not import keypair.');
                }

                chmod(__DIR__ . '/../.ssh/' . $this->keyPairName, 0400);
                chmod(__DIR__ . '/../.ssh/' . $this->keyPairName . '.pub', 0400);
            } catch (Ec2Exception $e) {
                //todo: handle exception
                throw $e;
            }
        }

        try {
            $this->client->createSecurityGroup([
                'GroupName'   => $this->securityGroupName,
                'Description' => 'Hummelflug security group',
            ]);

            $this->client->authorizeSecurityGroupIngress(array(
                'GroupName'     => $this->securityGroupName,
                'IpPermissions' => [
                    [
                        'IpProtocol' => 'tcp',
                        'FromPort'   => 80,
                        'ToPort'     => 80,
                        'IpRanges'   => [
                            ['CidrIp' => '0.0.0.0/0']
                        ],
                    ],
                    [
                        'IpProtocol' => 'tcp',
                        'FromPort'   => 22,
                        'ToPort'     => 22,
                        'IpRanges'   => [
                            ['CidrIp' => '0.0.0.0/0']
                        ],
                    ]
                ]
            ));
        } catch (Ec2Exception $e) {
            //todo: handle exception
        }
    }

    private function setup($id, InputInterface $input, OutputInterface $output)
    {
        $output->write('<info>Bumblebee: ' . $id . ': Setting up instance...</info>');

        $result = $this->client->runInstances([
            'ImageId'        => $this->configuration['main']['AMI'],
            'MinCount'       => 1,
            'MaxCount'       => 1,
            'InstanceType'   => 't1.micro',
            'KeyName'        => $this->keyPairName,
            'SecurityGroups' => [$this->securityGroupName,],
        ]);

        $instanceId = $result->get('Instances')[0]['InstanceId'];

        $waiter = $this->client->getWaiter(
            'InstanceRunning',
            [
                'InstanceIds' => [$instanceId],
            ]
        );

        $waiter->promise();

        $descriptions = $this->client->describeInstances([
            'InstanceId' => $instanceId,
        ]);

        $output->writeln('<info>done.</info>');

        $output->write('<info>Bumblebee ' . $id . ': Trying to determine the ip address...</info>');

        $attempts = 0;

        do {
            $waiter = $this->client->getWaiter(
                'InstanceRunning',
                [
                    'InstanceIds' => [$instanceId],
                ]
            );

            $waiter->promise();

            $descriptions = $this->client->describeInstances([
                'InstanceId' => $instanceId,
            ]);

            foreach ($descriptions->get('Reservations') as $reservation) {
                foreach ($reservation['Instances'] as $instance) {
                    if ($instance['InstanceId'] == $instanceId) {
                        if (array_key_exists('PublicIpAddress', $instance)) {
                            $ipAddress = $instance['PublicIpAddress'];
                        }
                    }
                }
            }

            $attempts++;
        } while (!isset($ipAddress) && $attempts < 3);

        if (!isset($ipAddress)) {
            throw new \Exception('Could not determine IP address.');
        }

        $output->writeln('<info>done: ' . $ipAddress . '.</info>');

        $output->write('<info>Bumblebee ' . $id . ': Wait until instance is ready to rumble...</info>');

        $waiter = $this->client->getWaiter(
            'InstanceStatusOk',
            [
                'InstanceIds' => [$instanceId],
            ]
        );

        $waiter->promise();

        $start = time();

        do {
            $waiter = $this->client->getWaiter(
                'InstanceStatusOk',
                [
                    'InstanceIds' => [$instanceId],
                ]
            );

            $waiter->promise();

            $con = @ssh2_connect($ipAddress);

            sleep(5);

            if (time() - $start > 600) {
                throw new \Exception('Operation timed out.');
            }
        } while ($con === false);

        $res = ssh2_auth_pubkey_file(
            $con,
            'ec2-user',
            __DIR__ . '/../.ssh/' . $this->keyPairName . '.pub',
            __DIR__ . '/../.ssh/' . $this->keyPairName
        );

        $output->writeln('<info>done.</info>');

        $stream = ssh2_exec($con, 'sudo yum -y update');
        stream_set_blocking($stream, true);
        $content = stream_get_contents($stream);

        $stream = ssh2_exec($con, 'sudo yum -y --enablerepo epel install siege');
        stream_set_blocking($stream, true);
        $content = stream_get_contents($stream);

        $stream = ssh2_exec($con, 'which siege');
        stream_set_blocking($stream, true);
        $content = stream_get_contents($stream);

        if (strpos($content, 'siege') === false) {
            throw new \Exception('Failed to install siege on bumblebee ' . $id);
        }

        $this->client->stopInstances(
            [
                'InstanceIds' => [$instanceId,],
            ]
        );

        $sem = sem_get(ftok(__DIR__ . '/../config/swarm.json', 0));

        sem_acquire($sem);

        $swarm = json_decode(file_get_contents(__DIR__ . '/../config/swarm.json'));

        $swarm->instances[] = $instanceId;

        file_put_contents(__DIR__ . '/../config/swarm.json', json_encode($swarm));

        sem_release($sem);

        $output->writeln('<info>done.</info>');
    }
}