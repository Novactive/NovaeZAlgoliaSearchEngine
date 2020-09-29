<?php

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Command\Demo;

use eZ\Publish\API\Repository\Repository;
use Faker\Factory;
use Novactive\Bundle\eZExtraBundle\Core\Manager\eZ\ContentType as ContentTypeManager;
use Novactive\Bundle\eZExtraBundle\Core\Manager\eZ\Content as ContentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateDemoContentTree extends Command
{
    protected static $defaultName = 'nova:ez:algolia:demo:create:contenttree';

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var ContentTypeManager
     */
    private $contentTypeManager;

    /**
     * @var ContentManager
     */
    private $contentManager;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @required
     */
    public function setDependencies(
        Repository $repository,
        ContentManager $content,
        ContentTypeManager $contentType
    ): void {
        $this->repository = $repository;
        $this->contentTypeManager = $contentType;
        $this->contentManager = $content;
    }

    protected function configure(): void
    {
        $this
            ->setHidden(true)
            ->setName(self::$defaultName)
            ->setDescription('Create a fake Content Treee.')
            ->addArgument('parentLocationId', InputArgument::REQUIRED, 'Where to create that fake Content Tree');
    }

    private function contentTypes(): array
    {
        return [
            'article' => [
                'type' => [
                    'nameSchema' => '<short_title|title>',
                    'isContainer' => true,
                    'name' => 'Article',
                ],
                'fields' => [
                    'title' => [
                        'type' => 'ezstring',
                        'name' => 'Title',
                        'isRequired' => true,
                        'isSearchable' => true,
                        'isTranslatable' => true,
                    ],
                    'short_title' => [
                        'type' => 'ezstring',
                        'name' => 'Short Title',
                        'isRequired' => false,
                        'isSearchable' => true,
                        'isTranslatable' => true,
                    ],
                    'intro' => [
                        'type' => 'ezrichtext',
                        'name' => 'Introduction',
                        'isRequired' => false,
                        'isSearchable' => true,
                        'isTranslatable' => true,
                    ],
                    'body' => [
                        'type' => 'ezrichtext',
                        'name' => 'Body',
                        'isRequired' => true,
                        'isSearchable' => true,
                        'isTranslatable' => true,
                    ],
                ]
            ]
        ];
    }

    private function wrapRichText(string $text): string
    {
        return trim(
            '<?xml version="1.0" encoding="UTF-8"?>
                <section 
                    xmlns="http://docbook.org/ns/docbook" 
                    xmlns:xlink="http://www.w3.org/1999/xlink" 
                    xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" 
                    xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" 
                    version="5.0-variant ezpublish-1.0"
                >
                    <para>'.$text.'</para>
            </section>'
        );
    }

    private function createUpdateContentTypes(): void
    {
        foreach ($this->contentTypes() as $contentTypeIdentifier => $contentTypeInfo) {

            $contentTypeData = $contentTypeInfo['type'];

            $contentTypeData['names'] = ['eng-GB' => $contentTypeData['name']];
            $contentTypeData['descriptions'] = ['eng-GB' => ''];
            $contentTypeData['urlAliasSchema'] = $contentTypeData['nameSchema'];
            unset($contentTypeData['name']);

            $contentTypeFieldDefinitionsData = [];
            $position = 1;
            foreach ($contentTypeInfo['fields'] as $identifier => $fieldInfo) {
                $fieldInfo['names'] = ['eng-GB' => $fieldInfo['name']];
                $fieldInfo['descriptions'] = ['eng-GB' => ''];
                $fieldInfo['identifier'] = $identifier;
                $fieldInfo['settings'] = [];
                $fieldInfo['fieldGroup'] = 'Content';
                $fieldInfo['position'] = $position++;
                unset($fieldInfo['name']);
                $contentTypeFieldDefinitionsData[] = $fieldInfo;
            }

            $this->contentTypeManager->createUpdateContentType(
                "nova_".$contentTypeIdentifier,
                'Content',
                $contentTypeData,
                $contentTypeFieldDefinitionsData
            );
            $this->io->progressAdvance(1);
        }
    }

    private function createUpdateContents(int $parentLocationId = 2, int $limit = 30): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < $limit; $i++) {
            // get a random from the list
            $remoteId = "something-{$i}";
            $this->contentManager->createUpdateContent(
                'article',
                $parentLocationId,
                [
                    'title' => $faker->sentence(3),
                    'short_title' => $faker->sentence(1),
                    'intro' => $this->wrapRichText($faker->sentence(10)),
                    'body' => $this->wrapRichText($faker->sentence(60)),
                ],
                $remoteId
            );
            $this->io->progressAdvance(1);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $contentCount = 30;
        $this->io->progressStart(\count($this->contentTypes()) + $contentCount);
        $this->createUpdateContentTypes();

        // pass the location id of the top location from input
        $this->createUpdateContents(2, $contentCount);

        $this->io->progressFinish();

        return Command::SUCCESS;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->repository->getPermissionResolver()->setCurrentUserReference(
            $this->repository->getUserService()->loadUserByLogin('admin')
        );

        parent::initialize($input, $output); // TODO: Change the autogenerated stub
    }

}
