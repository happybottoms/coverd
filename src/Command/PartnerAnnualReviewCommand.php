<?php

namespace App\Command;

use App\Configuration\AppConfiguration;
use App\Entity\Group;
use App\Entity\Partner;
use App\Workflow\PartnerWorkflow;
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

class PartnerAnnualReviewCommand extends Command
{
    protected static $defaultName = 'app:partner-review:run';

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

    private $tokenStorage;

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

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Run the partner annual review job.')
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Execute transitions on partners need to be moved if we are in the review window.'
            )
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force') !== false;
        $io = new SymfonyStyle($input, $output);
        $partnerRepo = $this->em->getRepository(Partner::class);

        $headers = ['Partner', 'Current Status', 'Planned Transition'];
        $rows = [];

        $now = new Moment('now');

        $start = new Moment($this->appConfig->get('partnerReviewStart'));
        $start->setYear((int) $now->getYear());
        $end = new Moment($this->appConfig->get('partnerReviewEnd'));
        $end->setYear((int) $now->getYear());

        $lastStart = new Moment($this->appConfig->get('partnerReviewLastStartRun'));
        $lastEnd = new Moment($this->appConfig->get('partnerReviewLastEndRun'));

        // We are not in a review period and we've finished the previous period
        if (!$now->isBetween($start, $end) && $lastEnd->isAfter($end)) {
            $io->success('No Partner Review action needed at this time.');
            return 0;
        }

        $io->text(sprintf('Partner Review Period: %s - %s', $start->format(), $end->format()));
        $io->text(sprintf('Last start date: %s', $lastStart->format()));
        $io->text(sprintf('Last end date: %s', $lastEnd->format()));

        // We have entered a new review period and have not started it.
        if ($now->isAfter($start) && $lastStart->isBefore($start)) {
            $activePartners = $this->em->getRepository(Partner::class)->findBy(['status' => Partner::STATUS_ACTIVE]);

            foreach ($activePartners as $partner) {
                $rows[] = [
                    $partner->getTitle(),
                    $partner->getStatus(),
                    PartnerWorkflow::TRANSITION_FLAG_FOR_REVIEW
                ];

                $this->workflowRegistry
                    ->get($partner)
                    ->apply($partner, PartnerWorkflow::TRANSITION_FLAG_FOR_REVIEW);

                if ($force) {
                    $this->appConfig->set('partnerReviewLastStartRun', $now->format());
                }
            }
        } elseif ($now->isAfter($end) && $lastEnd->isBefore($end)) {
            // We are passed the end of the review period, but have not completed it
            $activePartners = $this->em
                ->getRepository(Partner::class)
                ->findBy(['status' => Partner::STATUS_NEEDS_PROFILE_REVIEW]);

            foreach ($activePartners as $partner) {
                $rows[] = [
                    $partner->getTitle(),
                    $partner->getStatus(),
                    PartnerWorkflow::TRANSITION_FLAG_FOR_REVIEW_PAST_DUE
                ];

                $this->workflowRegistry
                    ->get($partner)
                    ->apply($partner, PartnerWorkflow::TRANSITION_FLAG_FOR_REVIEW_PAST_DUE);

                if ($force) {
                    $this->appConfig->set('partnerReviewLastEndRun', $now->format());
                }
            }
        } else {
            $io->success(sprintf(
                'Currently in an active review period. Next action will be taken after %s',
                $end->format()
            ));
            return 0;
        }

        $io->table($headers, $rows);

        if ($force) {
            $this->em->flush();
        } else {
            $io->warning(sprintf(
                '%d partner(s) are queued for annual review transitions. Use --force to update partner statuses',
                count($rows)
            ));
        }

        return 0;
    }
}
