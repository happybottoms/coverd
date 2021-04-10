<?php

namespace App\Command;

use App\Entity\Client;
use App\Workflow\ClientWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Workflow\Registry;

class ClientExpirationCommand extends Command
{
    protected static $defaultName = 'app:client-expiration';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Registry
     */
    private $workflowRegistry;

    public function __construct(EntityManagerInterface $em, Registry $workflowRegistry)
    {
        $this->em = $em;
        $this->workflowRegistry = $workflowRegistry;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Run the client expiration checks and transition expired clients. ')
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Execute transitions on expired clients. Otherwise, actions will only be reported.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = $input->getOption('force') !== false;
        $io = new SymfonyStyle($input, $output);
        $clientRepo = $this->em->getRepository(Client::class);

        $headers = ['Client', 'Partner', 'Expiration Reason', 'Expiration Date'];

        $agedOutClients = $clientRepo->findAllActiveAgedOut();

        $ageRows = array_map(function (Client $client) {
            return [
                (string) $client,
                (string) $client->getPartner(),
                'Age Expiration',
                $client->getAgeExpiresAt()->format('Y-m-d'),
            ];
        }, $agedOutClients);

        $maxDistributionClients = $clientRepo->findAllActiveMaxDistributions();

        $distRows = array_map(function (Client $client) {
            return [
                (string) $client,
                (string) $client->getPartner(),
                'Distribution Expiration',
                $client->getDistributionExpiresAt()->format('Y-m-d'),
            ];
        }, $maxDistributionClients);

        $rows = array_merge($ageRows, $distRows);

        $io->table($headers, $rows);

        if ($force) {
            foreach ($agedOutClients as $client) {
                $this->workflowRegistry
                    ->get($client)
                    ->apply($client, ClientWorkflow::TRANSITION_DEACTIVATE_EXPIRE);
            }

            foreach ($maxDistributionClients as $client) {
                $this->workflowRegistry
                    ->get($client)
                    ->apply($client, ClientWorkflow::TRANSITION_DEACTIVATE_EXPIRE);
            }

            $this->em->flush();

            $io->success(sprintf(
                '%d client(s) have been transitioned to "%s".',
                count($rows),
                ClientWorkflow::TRANSITION_DEACTIVATE_EXPIRE
            ));
        } else {
            $io->warning(sprintf(
                '%d client(s) are queued for expiration. Use --force to update client statuses',
                count($rows)
            ));
        }

        return 0;
    }
}
