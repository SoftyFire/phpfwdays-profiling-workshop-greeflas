<?php

namespace app\services;

use app\models\Article;
use app\models\ArticleTags;
use app\models\Tag;
use joshtronic\LoremIpsum;
use yii\helpers\ArrayHelper;

/**
 * Class ArticlesGenerator
 *
 * Generates random articles for testing purposes.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
class ArticlesGenerator
{
    /**
     * @var LoremIpsum
     */
    private $ipsum;

    private $tags = [];

    /**
     * ArticlesGenerator constructor.
     *
     * @param LoremIpsum $ipsum
     */
    function __construct(LoremIpsum $ipsum)
    {
        $this->ipsum = $ipsum;
    }

    /**
     * @param int $number Number of articles to be generated
     * @return Article[]
     */
    public function generate(int $number): array
    {
        $articles = [];
        for ($i = 0; $i < $number; $i++) {
            $article = $this->createRandomArticles();

            $articles[] = [
                'title' => $article->title,
                'text' => $article->text,
            ];
        }

        ArticleTags::getDb()->createCommand()
            ->batchInsert(Article::tableName(), ['title', 'text'], $articles)
            ->execute()
        ;

        return $articles;
    }

    /**
     * @return Article
     */
    private function createRandomArticles(): Article
    {
        $article = new Article([
            'title' => $this->generateRandomTitle(),
            'text' => $this->generateRandomText(),
        ]);
        $article->insert(false);
        $this->generateTagsForArticle($article);

        return $article;
    }

    /**
     * @return int
     */
    private function getRandomTag(): int
    {
        $tags = [
            'hit',
            'politics',
            'culture',
            'technologies',
            'health',
            'music',
            'cinema',
            'climate',
            'science',
            'nature',
            'photography',
            'biology',
        ];

        $i = mt_rand(0, count($tags) - 1);

        return $this->ensureTag($tags[$i]);
    }

    /**
     * @param string $name
     * @return int
     */
    private function ensureTag(string $name): int
    {
        if (isset($this->tags[$name])) {
            return  $this->tags[$name];
        }

        if ($tagId = Tag::find()->select('id')->where(['name' => $name])->scalar()) {
            $this->tags[$name] = $tagId;
        } else {
            $tag = new Tag(['name' => $name]);
            $tag->insert(false);
            $this->tags[$name] = $tag->id;
        }

        return $this->tags[$name];
    }

    /**
     * @param Article $article
     * @void
     */
    private function generateTagsForArticle($article): void
    {
        $count = mt_rand(1, 5);

        $xrefs = [];

        for ($i = 0; $i < $count; $i++) {
            $xrefs[] = [$article->id, $this->getRandomTag()];
        }

        ArticleTags::getDb()->createCommand()
            ->batchInsert(ArticleTags::tableName(), ['article_id', 'tag_id'], $xrefs)
            ->execute()
        ;
    }

    /**
     * @return string
     */
    private function generateRandomTitle(): string
    {
        return $this->ipsum->words(8);
    }

    /**
     * @return string
     */
    private function generateRandomText(): string
    {
        return $this->ipsum->paragraphs(2);
    }
}
