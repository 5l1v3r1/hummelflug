<?php

namespace Symfony\Component\Console\Command;

use Aws\Ec2\Ec2Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends Command
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
            ->setName('status')
            ->setDescription('Shows your swarms status.')
            ->setHelp('Shows your swarms status.')
            ->addOption(
                'config',
                null,
                InputOption::VALUE_REQUIRED,
                'Provide the path to the config file, please!'
            )
            ->addOption(
                'swarm',
                null,
                InputOption::VALUE_REQUIRED,
                'Provide the path to the swarm file, please!'
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
            )
            ->addOption(
                'AWSAccessKeyId',
                null,
                InputOption::VALUE_REQUIRED,
                'AWSAccessKeyId'
            )
            ->addOption(
                'AWSSecretKey',
                null,
                InputOption::VALUE_REQUIRED,
                'AWSSecretKey'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->keyPairName = $input->getOption('keypair');
        $this->securityGroupName = $input->getOption('groupname');

        if (!is_null($input->getOption('config'))) {
            $configFile = $input->getOption('config');
        } else {
            $configFile = __DIR__ . '/../config/config.ini';
        }

        if (!file_exists($configFile)) {
            throw new \Exception('Configuration file ' . $configFile . ' does not exists.');
        }

        $this->configuration = parse_ini_file($configFile, true);

        $awsKeyId = $input->getOption('AWSAccessKeyId');
        $awsSecretKey = $input->getOption('AWSSecretKey');

        $this->client = new Ec2Client([
            'credentials' => [
                'key' => $awsKeyId ?: $this->configuration['credentials']['AWSAccessKeyId'],
                'secret' => $awsSecretKey ?: $this->configuration['credentials']['AWSSecretKey'],
            ],
            'region' => $this->configuration['main']['region'],
            'version' => '2016-11-15',
        ]);

        if (!$this->client instanceof Ec2Client) {
            throw new \Exception('Could not create client.');
        }
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!is_null($input->getOption('swarm'))) {
            $swarmFile = $input->getOption('swarm');
        } else {
            $swarmFile = __DIR__ . '/../config/swarm.json';
        }

        if (!file_exists($swarmFile)) {
            throw new \Exception('Swarm file ' . $swarmFile . ' does not exists.');
        }

        $swarm = json_decode(file_get_contents($swarmFile));

        $descriptions = $this->client->describeInstances(
            [
                'InstanceIds' => $swarm->instances,
            ]
        );

        foreach ($descriptions->get('Reservations') as $reservation) {
            foreach ($reservation['Instances'] as $instance) {
                $output->writeln(
                    '<info>Bumblebee ' . $instance['InstanceId'] . ' is ' . $this->getStatus($instance) .'.</info>'
                );
            }
        }
    }

    /**
     * @param $instance
     *
     * @return string
     */
    private function getStatus($instance)
    {
        $states = [
            'pending' => 'waking up',
            'running' => 'alive',
            'stopping' => 'falling asleep',
            'stopped' => 'sleeping',
        ];

        $status = $instance['State']['Name'];

        if (array_key_exists($status, $states)) {
            return $states[$status];
        }

        return $status;
    }
}