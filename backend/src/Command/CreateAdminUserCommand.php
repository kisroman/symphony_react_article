<?php

namespace App\Command;

use App\Service\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Create admin user',
)]
class CreateAdminUserCommand extends Command
{
    /**
     * @param UserService $userService
     */
    public function __construct(private readonly UserService $userService)
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('firstName', InputArgument::REQUIRED, 'FirstName')
            ->addArgument('lastName', InputArgument::REQUIRED, 'LastName');
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Random\RandomException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');
        $firstName = $input->getArgument('firstName');
        $lastName = $input->getArgument('lastName');

        $user = $this->userService->createAndFlush($username, $firstName, $lastName, UserService::ROLE_ADMIN);

        $io->success('Admin user successfully created. User ID: ' . $user->getId());

        return Command::SUCCESS;
    }
}
