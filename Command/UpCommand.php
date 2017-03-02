<?php

namespace Symfony\Component\Console\Command;

use Aws\Ec2\Ec2Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpCommand extends Command
{
    private $defaultKeyPairName = 'hummelflug';
    private $defaultSecurityGroupName = 'hummelflug';

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
            ->setName('up')
            ->setDescription('Wakes up all the bumblebees.')
            ->setHelp('Wakes up all the bumblebees.')
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

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->keyPairName = $input->getOption('keypair');
        $this->securityGroupName = $input->getOption('groupname');

        $this->configuration = parse_ini_file(__DIR__ . '/../config/config.ini', true);

        $this->client = new Ec2Client([
            'key' => $this->configuration['credentials']['AWSAccessKeyId'],
            'secret' => $this->configuration['credentials']['AWSSecretKey'],
            'region' => $this->configuration['main']['region'],
            'version' => '2016-11-15',
        ]);

        if (!$this->client instanceof Ec2Client) {
            throw new \Exception('Could not create client.');
        }
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $swarm = json_decode(file_get_contents(__DIR__ . '/../config/swarm.json'));

        $output->writeln('<info>Waking up the swarm.</info>');

        $this->client->startInstances(
            [
                'InstanceIds' => $swarm->instances,
            ]
        );

        $waiter = $this->client->getWaiter(
            'InstanceRunning',
            [
                'InstanceIds' => $swarm->instances,
            ]
        );

        $waiter->promise();

        $pids = [];

        foreach ($swarm->instances as $instance) {

            $pid = pcntl_fork();

            if ($pid == -1) {
                throw new \Exception('Starting up bumblebee no ' . $instance . ' failed');
            } else if ($pid) {
                $pids[$pid] = $instance;
            } else {
                $this->waitForInstance($instance, $input, $output);
                exit;
            }
        }

        while (!empty($pids)) {
            $pid = pcntl_wait($status);

            unset ($pids[$pid]);
        }

        $output->writeln('<info>Your swarm is ready to rumble.</info>');
    }

    private function waitForInstance($instanceId, InputInterface $input, OutputInterface $output)
    {
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

        $output->writeln('<info>Bumblebee ' . $instanceId . ' is alive. (IP address: ' . $ipAddress . ')</info>');

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

        $output->writeln('<info>Bumblebee ' . $instanceId . ' buzzes aggressively.</info>');
    }
}