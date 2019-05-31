<?php

namespace app\tests;

use app\services\ArticlesGenerator;
use Blackfire\Bridge\PhpUnit\TestCaseTrait;
use Blackfire\Profile\Configuration;
use Blackfire\Profile\Metric;
use joshtronic\LoremIpsum;
use PHPUnit\Framework\TestCase;

/**
 * Class ArticlesGeneratorTest
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
class ArticlesGeneratorTest extends TestCase
{
    use TestCaseTrait;
    /**
     * @var ArticlesGenerator
     */
    protected $generator;
    public function setUp()
    {
        $this->generator = new ArticlesGenerator(new LoremIpsum());
    }

    public function testGeneratesArticles()
    {
        $config = new Configuration();
        // define some assertions
        $config
            ->defineMetric(new Metric('tags.search', '=app\models\Tag::find'))
            ->defineMetric(new Metric('ensure_tag', '=app\services\ArticlesGenerator::ensureTag'))
            ->assert('metrics.sql.queries.count < 20', 'SQL queries count')
            ->assert('metrics.tags.search.count < metrics.ensure_tag.count', 'Cache is used')
        ;

        $profile = $this->assertBlackfire($config, function () {
            $articles = $this->generator->generate(1);
            $article = $articles[0];
            $this->assertNotEmpty($article['title']);
            $this->assertNotEmpty($article['text']);
        });
    }
}
