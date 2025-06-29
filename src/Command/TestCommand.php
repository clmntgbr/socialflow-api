<?php

namespace App\Command;

use App\Application\Command\PublishCluster;
use App\Repository\Post\ClusterRepository;
use App\Service\Publish\PublishServiceFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:test',
    description: 'Add a short description for your command',
)]
class TestCommand extends Command
{
    public function __construct(
        private ClusterRepository $clusterRepository,
        private PublishServiceFactory $publishServiceFactory,
        private MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cluster = $this->clusterRepository->findOneBy(['id' => '93db3670-c529-4ee6-8fd0-c6394c8f7bbe']);

        $this->messageBus->dispatch(new PublishCluster(clusterId: $cluster->getId()), [
            new AmqpStamp('async'),
        ]);

        return Command::SUCCESS;
    }
}
