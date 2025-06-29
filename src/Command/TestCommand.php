<?php

namespace App\Command;

use App\Application\Command\PublishPost;
use App\Repository\Post\PostRepository;
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
        private PostRepository $postRepository,
        private PublishServiceFactory $publishServiceFactory,
        private MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $post = $this->postRepository->findOneBy(['id' => '10508a5c-e5fc-4cfa-a19e-ef6d8aa3eda9']);

        $this->messageBus->dispatch(new PublishPost(
            postId: $post->getId(),
        ), [
            new AmqpStamp('async'),
        ]);

        return Command::SUCCESS;
    }
}
