<?php
/**
 * User: anubis
 * Date: 05.08.13
 * Time: 22:36
 */

namespace popcorn\tests\model;

use popcorn\model\content\Image;
use popcorn\model\content\ImageFactory;
use popcorn\model\content\NullImage;
use popcorn\model\dataMaps\FashionBattleDataMap;
use popcorn\model\dataMaps\PollPostDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\posts\FashionBattlePost;
use popcorn\model\posts\PhotoArticlePost;
use popcorn\model\posts\NewsPostBuilder;
use popcorn\model\posts\PollPost;
use popcorn\model\posts\PostFactory;
use popcorn\model\tags\Tag;
use popcorn\model\tags\TagFactory;
use popcorn\model\voting\Opinion;
use popcorn\model\voting\VotingFactory;
use popcorn\tests\TestDataGenerator;

/**
 * Class PostFactoryTest
 * @package popcorn\tests\model
 */
class PostFactoryTest extends PopcornTest {

    public function testDefaultNewsPost() {
        $post = new PhotoArticlePost();
        $post->setAnnounce('test');

        $tag = new Tag('tag');
        $tagData = new TagDataMap();
        $tagData->save($tag);

        $img = new Image();
        $img->setName('img.png');
        ImageFactory::save($img);

        $post->addTag($tag);
        $post->addImage($img);
        $post->setMainImageId($img);

        PostFactory::savePost($post);
        $this->assertGreaterThan(0, $post->getId());

        $savedPost = PostFactory::getPost($post->getId());
        $this->assertEquals(array($post), array($savedPost));
        $this->assertCount(1, $savedPost->getTags());
        $this->assertInstanceOf(get_class($post), $savedPost);
    }

    public function testLoadPost() {
        TestDataGenerator::addTopNewsPosts(2);
        $savedPost = PostFactory::getPost(2);
        $this->assertEquals('test post 1', $savedPost->getName());
    }

    public function testGetPosts() {
        TestDataGenerator::addTopNewsPosts(2);
        $posts = PostFactory::getPosts();
        $this->assertCount(2, $posts);
        $posts = PostFactory::getPosts(1, 1);
        $this->assertCount(1, $posts);
    }

    public function testSearchPost() {
        TestDataGenerator::addTopNewsPosts(20);
        $posts = PostFactory::searchPosts('2');
        $this->assertCount(2, $posts);
    }

    public function testTopCronPosts() {
        TestDataGenerator::addTopNewsPosts(10);
        $topPosts = PostFactory::getTopPosts(6);
        $this->assertCount(6, $topPosts);
        $this->assertEquals(10, $topPosts[0]->getId());
        $this->assertEquals(90, $topPosts[1]->getComments());
        $posts = PostFactory::getPosts();
        $this->assertEquals(10, $posts[0]->getId());
        $this->assertEquals(9, $posts[1]->getId());
    }

    public function testRemovePosts() {
        TestDataGenerator::addTopNewsPosts(5);
        $posts = PostFactory::getPosts();
        $this->assertCount(5, $posts);
        PostFactory::removePost($posts[1]->getId());
        $posts = PostFactory::getPosts();
        $this->assertCount(4, $posts);
    }

    public function testEditPost() {
        $post = new PhotoArticlePost();
        $post->setAnnounce('test');

        $tag = new Tag('tag');
        $tagData = new TagDataMap();
        $tagData->save($tag);

        $img = new Image();
        $img->setName('img.png');
        ImageFactory::save($img);

        $post->addTag($tag);
        $post->addImage($img);
        $post->setMainImageId($img);

        PostFactory::savePost($post);

        $post->clearImages();
        $post->setContent('edited');
        PostFactory::savePost($post);

        $savedPost = PostFactory::getPost($post->getId());
        $this->assertEquals(array($post), array($savedPost));
    }

    public function testPollPost() {
        PostFactory::setDataMap(new PollPostDataMap());
        $post = new PollPost();
        PostFactory::savePost($post);
        $this->assertGreaterThan(0, $post->getId());
        $opinions = array(
            new Opinion(),
            new Opinion(),
            new Opinion(),
        );
        $opinions[0]->setTitle('title 1');
        $opinions[1]->setTitle('title 2');
        $opinions[2]->setTitle('title 3');
        $post->createVote($opinions, 'voting awsome title');
        $savedPost = PostFactory::getPost($post->getId());
        $this->assertInstanceOf(get_class($post), $savedPost);
        $this->assertEquals(array($post), array($savedPost));
        VotingFactory::vote($post->getVoting()->getId(), $post->getVoting()->getOpinion(0)->getId());
        $savedPost = PostFactory::getPost($post->getId());
        $this->assertEquals(get_object_vars(VotingFactory::getByParent($post->getId())),
                            get_object_vars($savedPost->getVoting()));
    }

    public function testFashionBattlePost() {
        PostFactory::setDataMap(new FashionBattleDataMap());
        TestDataGenerator::addPersons(2);
        $post = new FashionBattlePost();
        PostFactory::savePost($post);
        $post->createVoting(1, 2);
        $this->assertGreaterThan(0, $post->getId());
        $savedPost = PostFactory::getPost($post->getId());
        $this->assertInstanceOf(get_class($post), $savedPost);
        $this->assertEquals(array($post), array($savedPost));
        VotingFactory::vote($post->getVoting()->getId(), $post->getVoting()->getOpinion(0)->getId());
        $savedPost = PostFactory::getPost($post->getId());
        $this->assertEquals(get_object_vars(VotingFactory::getByParent($post->getId())),
                            get_object_vars($savedPost->getVoting()));
        PostFactory::resetDataMap();
    }

    /**
     * @expectedException \popcorn\model\exceptions\SaveFirstException
     */
    public function testPollPostUnsaved() {
        PostFactory::setDataMap(new PollPostDataMap());
        $post = new PollPost();
        $post->createVote(array(new Opinion(array('title' => '1'))));
        PostFactory::resetDataMap();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAttemptToChangeId() {
        TestDataGenerator::addTopNewsPosts(1);
        $post = PostFactory::getPost(1);
        $post->setId(2);
    }

    public function testCreateByBuilder() {
        PostFactory::resetDataMap();
        $post = NewsPostBuilder::create()
            ->name('test')
            ->content('content')
            ->announce('announce')
            ->source('example.com')
            ->createDate(time())
            ->mainImage(new NullImage())
            ->publish()
            ->allowComment()
            ->uploadRSS()
            ->build();
        PostFactory::savePost($post);
        $saved = PostFactory::getPost($post->getId());
        $this->assertEquals($post, $saved);

        $builder = NewsPostBuilder::create()
            ->name('name')
            ->content('content')
            ->disAllowComment()
            ->dontPublish()
            ->dontUploadRSS();
        $post = PostFactory::createFromBuilder($builder);
        $saved = PostFactory::getPost($post->getId());
        $this->assertEquals($post, $saved);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuilderException_Name() {
        NewsPostBuilder::create()->build();
    }

    public function testGetPostsByTags() {
        /** @var NewsPost[] $posts */
        $posts = array(
            NewsPostBuilder::create()
                           ->name('test 1')
                           ->content('test 1')
                           ->build(),
            NewsPostBuilder::create()
                           ->name('test 2')
                           ->content('test 2')
                           ->build()
        );
        $tag = new Tag('test', Tag::PERSON);
        TagFactory::save($tag);
        $posts[1]->addTag($tag);
        PostFactory::savePost($posts[0]);
        PostFactory::savePost($posts[1]);

        $savedPosts = PostFactory::getByTag($tag->getId());
        $this->assertEquals(array($posts[1]), $savedPosts);
    }

}
