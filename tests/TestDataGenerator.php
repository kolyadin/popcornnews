<?php
/**
 * User: anubis
 * Date: 17.09.13 15:14
 */

namespace popcorn\tests;


use popcorn\model\content\NullImage;
use popcorn\model\persons\PersonBuilder;
use popcorn\model\persons\PersonFactory;
use popcorn\model\posts\PhotoArticlePost;
use popcorn\model\posts\PostFactory;
use popcorn\model\system\users\User;
use popcorn\model\system\users\UserFactory;
use popcorn\model\system\users\UserInfo;
use popcorn\model\system\users\UserSettings;

class TestDataGenerator {

    public static function addTopNewsPosts($count = 10) {
        for($i = 0; $i < $count; $i++) {
            $post = new PhotoArticlePost();
            $post->setName('test post '.$i);
            $post->setAnnounce('announce '.$i);
            $post->setComments(($i + 1) * $count);
            $post->setCreateDate(time() + $i);
            $post->setPublished(true);
            PostFactory::savePost($post);
        }
    }

    public static function addPersons($count = 10) {
        for($i = 0; $i < $count; $i++) {
            $person = PersonBuilder::create()->name('person '.$i)->englishName('person '.$i)->build();
            PersonFactory::savePerson($person);
        }
    }

    public static function addUser($count = 10) {
        for($i = 0; $i < $count; $i++) {
            $user = new User();
            $user->setEmail('test'.rand().'@example.com');
            $user->setPassword('123');
            $user->setEnabled(1);
            $user->setNick('user '.$i);
            $user->setType(User::USER);
            $user->setAvatar(new NullImage());
            $user->setUserSettings(new UserSettings());
            $user->setUserInfo(new UserInfo());
            UserFactory::save($user);
        }
    }
}