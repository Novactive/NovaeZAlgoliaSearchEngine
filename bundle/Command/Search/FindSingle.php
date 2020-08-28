<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Command\Search;

use eZ\Publish\API\Repository\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

final class FindSingle extends Command
{
    protected static $defaultName = 'nova:ez:algolia:find:single';

    /**
     * @var Repository
     */
    private $repository;

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Fetching the single Content by Id.')
            ->addArgument('contentId', InputArgument::REQUIRED, 'Content Id');
    }

    /**
     * @required
     */
    public function setDependencies(Repository $repository): void
    {
        $this->repository = $repository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $contentId = $input->getArgument('contentId');

        $criterion = new Criterion\ContentId($contentId);

        //$this->repository->getPermissionResolver()->setCurrentUserReference($this->repository->getUserService()->loadUserByLogin('admin'));

        $result = $this->repository->getSearchService()->findSingle($criterion);
        $io->newLine();
        $output->writeln($result->getName());

        $io->success('Done.');

        return 0;
    }
}