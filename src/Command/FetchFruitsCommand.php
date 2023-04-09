<?php

namespace App\Command;

use App\Entity\Fruit;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'fruits:fetch',
    description: 'Fetching fruits from the API https://fruityvice.com/api with the endpoint /fruit/all',
)]
class FetchFruitsCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private MailerInterface $mailer;
    private Connection $connection;

    public function __construct(EntityManagerInterface $entityManager, MailerInterface $mailer, Connection $connection)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->connection = $connection;
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {


        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', 'https://fruityvice.com/api/fruit/all');

        $fruitsDataFromAPI = json_decode($response->getContent());

        if (empty($fruitsDataFromAPI)) {
            $output->writeln('No fruits found from FruityVice');
            return Command::FAILURE;
        }

        $platform = $this->connection->getDatabasePlatform();
        $this->connection->executeUpdate($platform->getTruncateTableSQL('fruit'));

        $numberOfRecordsFetched = count($fruitsDataFromAPI);
        $progressBar = new ProgressBar($output, $numberOfRecordsFetched);
        $progressBar->setFormat(sprintf('%s Fetched fruits: <info>%%item%%</info>',
        $progressBar->getFormatDefinition('very_verbose'))); // the new format
        $progressBar->start();

        foreach ($fruitsDataFromAPI as $key => $ApiFruit){
            $fruit = new Fruit();
            $fruit->setName($ApiFruit->name);
            $fruit->setGenus($ApiFruit->genus);
            $fruit->setFamily($ApiFruit->family);
            $fruit->setFruitOrder($ApiFruit->order);
            $fruit->setNutritions(json_encode($ApiFruit->nutritions));

            $repository = $this->entityManager->getRepository(Fruit::class);
            $repository->save($fruit, true);

            usleep(100000);
            $progressBar->setMessage($key+1, 'item');
            $progressBar->advance();
        }

        $progressBar->finish();

        $output->writeln(PHP_EOL.PHP_EOL.'Let me remind you through email that your basket is full of fruits!');
        $email = (new Email())
            ->from('hello@example.com')
            ->to('raza.mehdi@thehexaa.com')
            ->subject('Hello Fruity!')
            ->text(
                "Your fruit basket appears to be quite full.
Would you like to take a moment to have a look inside and see what's available?"
            );

        $this->mailer->send($email);
        $output->writeln(PHP_EOL.'Email sent successfully!');
        return Command::SUCCESS;
    }
}
