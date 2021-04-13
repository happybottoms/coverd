<?php

namespace App\Command;

use App\Configuration\AppConfiguration;
use App\Entity\Client;
use App\Entity\Group;
use App\Workflow\ClientWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Moment\Moment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Workflow\Registry;

class ClientAnnualReviewCommand extends Command
{
    protected static $defaultName = 'app:client-review:run';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Registry
     */
    private $workflowRegistry;

    /**
     * @var AppConfiguration
     */
    private $appConfig;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var Moment */
    private $reviewStartAt;

    /** @var Moment */
    private $reviewEndAt;

    /** @var Moment */
    private $lastStartAt;

    /** @var Moment */
    private $lastEndAt;

    public function __construct(
        EntityManagerInterface $em,
        Registry $workflowRegistry,
        AppConfiguration $appConfiguration,
        TokenStorageInterface $storage
    ) {
        $this->em = $em;
        $this->workflowRegistry = $workflowRegistry;
        $this->appConfig = $appConfiguration;

        $this->tokenStorage = $storage;

        $token = new UsernamePasswordToken(
            'system',
            null,
            'main',
            Group::AVAILABLE_ROLES
        );

        $this->tokenStorage->setToken($token);

        $now = new Moment('now');

        $this->reviewStartAt = new Moment($this->appConfig->get('clientReviewStart'));
        $this->reviewStartAt->setYear((int) $now->getYear());
        $this->reviewEndAt = new Moment($this->appConfig->get('clientReviewEnd'));
        $this->reviewEndAt->setYear((int) $now->getYear());

        $this->lastStartAt = new Moment($this->appConfig->get('clientReviewLastStartRun'));
        $this->lastEndAt = new Moment($this->appConfig->get('clientReviewLastEndRun'));

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Run the client annual review job.')
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Execute transitions on clients need to be moved if we are in the review window.'
            )
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force') !== false;
        $io = new SymfonyStyle($input, $output);
        $clientRepo = $this->em->getRepository(Client::class);

        $now = new Moment('now');

        $headers = ['Client', 'Current Status', 'Partner', 'Planned Transition'];
        $rows = [];

        $io->text(
            sprintf(
                'Client Review Period: %s - %s',
                $this->reviewStartAt->format(),
                $this->reviewEndAt->format()
            )
        );
        $io->text(sprintf('Last start date: %s', $this->lastStartAt->format()));
        $io->text(sprintf('Last end date: %s', $this->lastEndAt->format()));

        // We are not in a review period and we've finished the previous period
        if ($this->isBeforeYearReview() || $this->isAfterYearCompleteReview()) {
            $io->success('No Client Review action needed at this time.');
            return 0;
        }

        // We are in a review period, but it's started so we don't need to do anything
        if ($this->isInReviewPeriod() && $this->isCurrentYearReviewStarted()) {
            $io->success(sprintf(
                'Currently in an started active review period. Next action will be taken after %s',
                $this->reviewEndAt->format()
            ));
            return 0;
        }

        // We have entered a new review period and have not started it.
        if ($this->isInReviewPeriod() && !$this->isCurrentYearReviewStarted()) {
            $activeClients = $clientRepo->findBy(['status' => Client::STATUS_ACTIVE]);

            foreach ($activeClients as $client) {
                $rows[] = [
                    (string) $client,
                    $client->getStatus(),
                    $client->getPartner() ? $client->getPartner()->getTitle() : null,
                    ClientWorkflow::TRANSITION_FLAG_FOR_REVIEW
                ];

                $this->workflowRegistry
                    ->get($client)
                    ->apply($client, ClientWorkflow::TRANSITION_FLAG_FOR_REVIEW);

                if ($force) {
                    $this->appConfig->set('clientReviewLastStartRun', $now->format());
                }
            }
        } elseif ($this->isAfterYearReviewEnd() && !$this->isCurrentYearReviewComplete()) {
            // We are passed the end of the review period, but have not completed it
            $activeClients = $clientRepo->findBy(['status' => Client::STATUS_NEEDS_REVIEW]);

            foreach ($activeClients as $client) {
                $rows[] = [
                    (string) $client,
                    $client->getStatus(),
                    $client->getPartner() ? $client->getPartner()->getTitle() : null,
                    ClientWorkflow::TRANSITION_FLAG_FOR_REVIEW_PAST_DUE
                ];

                $this->workflowRegistry
                    ->get($client)
                    ->apply($client, ClientWorkflow::TRANSITION_FLAG_FOR_REVIEW_PAST_DUE);

                if ($force) {
                    $this->appConfig->set('clientReviewLastEndRun', $now->format());
                }
            }
        } else {
            $io->error('We should not get to this code.');
            return 1;
        }

        $io->table($headers, $rows);

        if ($force) {
            $this->em->flush();
        } else {
            $io->warning(sprintf(
                '%d client(s) are queued for annual review transitions. Use --force to update client statuses',
                count($rows)
            ));
        }

        return 0;
    }

    private function isInReviewPeriod(): bool
    {
        $now = new Moment();
        return $now->isBetween($this->reviewStartAt, $this->reviewEndAt);
    }

    private function isAfterYearReviewEnd(): bool
    {
        $now = new Moment();
        return $now->isAfter($this->reviewEndAt);
    }

    private function isCurrentYearReviewComplete(): bool
    {
        return $this->lastEndAt->isAfter($this->reviewEndAt);
    }

    private function isCurrentYearReviewStarted(): bool
    {
        return $this->lastStartAt->isAfter($this->reviewStartAt);
    }

    private function isBeforeYearReview(): bool
    {
        $now = new Moment();
        return !$this->isCurrentYearReviewStarted() && $now->isBefore($this->reviewStartAt);
    }

    private function isAfterYearCompleteReview(): bool
    {
        $now = new Moment();
        return $this->isCurrentYearReviewComplete() && $now->isAfter($this->reviewEndAt);
    }
}
