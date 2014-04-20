CREATE TABLE IF NOT EXISTS `pn_albums` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT,
  `createTime` INT(11) NOT NULL,
  `poster`     INT(11) NOT NULL,
  `owner`      INT(11) NOT NULL,
  `title`      VARCHAR(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_album_images` (
  `id`         INT(11)    NOT NULL AUTO_INCREMENT,
  `albumId`    INT(11)    NOT NULL,
  `imageId`    INT(11)    NOT NULL,
  `enable`     TINYINT(1) NOT NULL DEFAULT '1',
  `order`      INT(11)    NOT NULL DEFAULT '0',
  `createTime` INT(11)    NOT NULL,
  PRIMARY KEY (`id`, `albumId`, `imageId`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_comments_news` (
  `id`         INT(11)     NOT NULL AUTO_INCREMENT,
  `postId`     INT(11)     NOT NULL,
  `date`       INT(11)     NOT NULL,
  `owner`      INT(11)     NOT NULL,
  `parent`     INT(11) DEFAULT NULL,
  `content`    TEXT        NOT NULL,
  `editDate`   INT(11)     NOT NULL DEFAULT '0',
  `ip`         VARCHAR(16) NOT NULL,
  `abuse`      INT(11)     NOT NULL DEFAULT '0',
  `ratingDown` INT(11)     NOT NULL DEFAULT '0',
  `ratingUp`   INT(11)     NOT NULL DEFAULT '0',
  `deleted`    TINYINT(1)  NOT NULL DEFAULT '0',
  `level`      INT(11)     NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `newsId` (`postId`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_comments_news_abuse` (
  `commentId` INT(11) NOT NULL,
  `userId`    INT(11) NOT NULL,
  UNIQUE KEY `commentAbuse` (`commentId`, `userId`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_comments_news_subscribe` (
  `newsId` INT(11) NOT NULL,
  `userId` INT(11) NOT NULL,
  UNIQUE KEY `subs` (`newsId`, `userId`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_comments_news_vote` (
  `commentId` INT(11) NOT NULL,
  `userId`    INT(11) NOT NULL,
  UNIQUE KEY `comments_vote` (`commentId`, `userId`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_comments_talks` (
  `id`         INT(11)     NOT NULL AUTO_INCREMENT,
  `postId`     INT(11)     NOT NULL,
  `date`       INT(11)     NOT NULL,
  `owner`      INT(11)     NOT NULL,
  `parent`     INT(11) DEFAULT NULL,
  `content`    TEXT        NOT NULL,
  `editDate`   INT(11)     NOT NULL DEFAULT '0',
  `ip`         VARCHAR(16) NOT NULL,
  `abuse`      INT(11)     NOT NULL DEFAULT '0',
  `ratingDown` INT(11)     NOT NULL DEFAULT '0',
  `ratingUp`   INT(11)     NOT NULL DEFAULT '0',
  `deleted`    TINYINT(1)  NOT NULL DEFAULT '0',
  `level`      INT(11)     NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `newsId` (`postId`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_comments_talks_abuse` (
  `commentId` INT(11) NOT NULL,
  `userId`    INT(11) NOT NULL,
  UNIQUE KEY `commentAbuse` (`commentId`, `userId`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_comments_talks_vote` (
  `commentId` INT(11) NOT NULL,
  `userId`    INT(11) NOT NULL,
  UNIQUE KEY `comments_vote` (`commentId`, `userId`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_dictionary_news_article` (
  `id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_dictionary_news_tag` (
  `id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_dictionary_test` (
  `id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_groups` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(200) NOT NULL,
  `description` TEXT,
  `createTime`  DATETIME     NOT NULL,
  `editTime`    DATETIME     NOT NULL,
  `private`     INT(1)       NOT NULL DEFAULT '0',
  `owner`       INT(11)      NOT NULL,
  `poster`      INT(11)      NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_groups_tags` (
  `groupId` INT(11) DEFAULT NULL,
  `tagId`   INT(11) NOT NULL
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_images` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(250) NOT NULL,
  `title`       VARCHAR(250) DEFAULT NULL,
  `source`      VARCHAR(250) DEFAULT NULL,
  `zoomable`    INT(1)       NOT NULL DEFAULT '0',
  `description` TEXT,
  `createTime`  INT(11)      NOT NULL DEFAULT '0',
  `width`       INT(11)      NOT NULL DEFAULT '0',
  `height`      INT(11)      NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_kids` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `firstParent`  INT(11) DEFAULT NULL,
  `secondParent` INT(11) DEFAULT NULL,
  `name`         VARCHAR(250) NOT NULL,
  `description`  TEXT,
  `birthDate`    DATE DEFAULT NULL,
  `voting`       INT(11)      NOT NULL,
  `photo`        INT(11)      NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_log` (
  `id`      INT(11)  NOT NULL AUTO_INCREMENT,
  `adminId` INT(11)  NOT NULL,
  `time`    DATETIME NOT NULL,
  `path`    TEXT     NOT NULL,
  `get`     TEXT     NOT NULL,
  `post`    TEXT     NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_meetings` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `firstPerson`  INT(11)      NOT NULL,
  `secondPerson` INT(11)      NOT NULL,
  `title`        VARCHAR(250) NOT NULL,
  `description`  TEXT         NOT NULL,
  PRIMARY KEY (`id`, `firstPerson`, `secondPerson`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_news` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(200) NOT NULL,
  `updateDate`   INT(11) DEFAULT NULL,
  `createDate`   INT(11)      NOT NULL DEFAULT '0',
  `editDate`     INT(11)      NOT NULL DEFAULT '0',
  `content`      TEXT         NOT NULL,
  `allowComment` INT(11)      NOT NULL DEFAULT '1',
  `published`    INT(11)      NOT NULL DEFAULT '0',
  `views`        INT(11)      NOT NULL DEFAULT '0',
  `comments`     INT(11)      NOT NULL DEFAULT '0',
  `type`         VARCHAR(200) NOT NULL,
  `announce`     TEXT         NOT NULL,
  `source`       VARCHAR(250) DEFAULT NULL,
  `sent`         INT(1)       NOT NULL DEFAULT '0',
  `uploadRSS`    INT(1)       NOT NULL DEFAULT '0',
  `mainImageId`  INT(11)      NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_news_images` (
  `newsId`  INT(11) NOT NULL,
  `imageId` INT(11) NOT NULL
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_news_tags` (
  `newsId` INT(11) DEFAULT NULL,
  `tagId`  INT(11) NOT NULL
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_opinions` (
  `id`       INT(11)      NOT NULL AUTO_INCREMENT,
  `votingId` INT(11)      NOT NULL,
  `title`    VARCHAR(250) NOT NULL,
  PRIMARY KEY (`id`, `votingId`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_persons` (
  `id`                INT(11)      NOT NULL AUTO_INCREMENT,
  `name`              VARCHAR(200) NOT NULL,
  `englishName`       VARCHAR(200) NOT NULL,
  `genitiveName`      VARCHAR(200) NOT NULL,
  `prepositionalName` VARCHAR(200) NOT NULL,
  `info`              TEXT,
  `source`            VARCHAR(200) DEFAULT NULL,
  `photo`             INT(11) DEFAULT NULL,
  `birthDate`         DATE DEFAULT NULL,
  `showInCloud`       INT(1)       NOT NULL DEFAULT '1',
  `sex`               INT(1)       NOT NULL DEFAULT '0',
  `isSinger`          INT(1)       NOT NULL DEFAULT '0',
  `allowFacts`        INT(1)       NOT NULL DEFAULT '1',
  `isWidgetAvailable` INT(1)       NOT NULL DEFAULT '1',
  `widgetPhoto`       INT(11) DEFAULT NULL,
  `widgetFullPhoto`   INT(11) DEFAULT NULL,
  `vkPage`            VARCHAR(200) DEFAULT NULL,
  `twitterLogin`      VARCHAR(200) DEFAULT NULL,
  `pageName`          TEXT,
  `nameForBio`        TEXT,
  `published`         TINYINT(1)   NOT NULL DEFAULT '0',
  `urlName` VARCHAR(250) NOT NULL,
  `look`    INT(11)      NOT NULL DEFAULT '0',
  `style`   INT(11)      NOT NULL DEFAULT '0',
  `talent`  INT(11)      NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_persons_link` (
  `firstId`  INT(11) NOT NULL,
  `secondId` INT(11) NOT NULL,
  UNIQUE KEY `link` (`firstId`, `secondId`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_poll` (
  `id`       INT(11) NOT NULL AUTO_INCREMENT,
  `newsId`   INT(11) NOT NULL,
  `question` TEXT    NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_poll_opinions` (
  `id`      INT(11) NOT NULL AUTO_INCREMENT,
  `pollId`  INT(11) NOT NULL,
  `opinion` TEXT    NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_poll_votes` (
  `id`        INT(11)     NOT NULL AUTO_INCREMENT,
  `IP`        VARCHAR(16) NOT NULL,
  `userId`    INT(11)     NOT NULL,
  `date`      INT(11)     NOT NULL DEFAULT '0',
  `opinionId` INT(11)     NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_tags` (
  `id`   INT(11)      NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `type` INT(11)      NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_talks` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `createTime` DATETIME     NOT NULL,
  `owner`      INT(11)      NOT NULL,
  `title`      VARCHAR(200) NOT NULL,
  `content`    TEXT,
  `rating`     INT(11)      NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_users` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `email`        VARCHAR(200) NOT NULL,
  `password`     VARCHAR(200) NOT NULL,
  `type`       INT(2)  NOT NULL DEFAULT '1'
  COMMENT '1 - user\n2 - moderator\n3 - editor\n4 - admin',
  `enabled`      INT(1)       NOT NULL DEFAULT '0',
  `nick`         VARCHAR(200) NOT NULL,
  `avatar`     INT(11) NOT NULL DEFAULT '0',
  `rating`       INT(11)      NOT NULL DEFAULT '0',
  `banned`       INT(1)       NOT NULL DEFAULT '0',
  `userInfo`     INT(11)      NOT NULL,
  `userSettings` INT(11)      NOT NULL,
  `lastVisit`    INT(11)      NOT NULL DEFAULT '0',
  `createTime` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_users_info` (
  `id`            INT(11) NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(200) DEFAULT NULL,
  `sex`           INT(1)  NOT NULL DEFAULT '0'
  COMMENT '0 - unknown\n1 - male\n2 - female',
  `credo`         TEXT,
  `birthDate`     INT(11) DEFAULT NULL,
  `countryId`     INT(11) NOT NULL DEFAULT '0',
  `cityId`        INT(11) NOT NULL DEFAULT '0',
  `married`       INT(1)  NOT NULL DEFAULT '0'
  COMMENT '0 - unknown\n1 - single\n2 - married',
  `meetPerson`    INT(11) NOT NULL DEFAULT '0',
  `points`        INT(11) NOT NULL DEFAULT '0',
  `activist`      INT(1)  NOT NULL DEFAULT '0'
  COMMENT 'флаг-юзер активист месяца',
  `activistCount` INT(11) NOT NULL DEFAULT '0'
  COMMENT 'сколько раз был активистом',
  `banDate`       INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_users_settings` (
  `id`             INT(11) NOT NULL AUTO_INCREMENT,
  `showBirthDate`  INT(1)  NOT NULL DEFAULT '0',
  `dailySubscribe` INT(1)  NOT NULL DEFAULT '1',
  `alertMessage`   INT(1)  NOT NULL DEFAULT '1',
  `alertGuestBook` INT(1)  NOT NULL DEFAULT '1',
  `canInvite`      INT(1)  NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_votes` (
  `id`        INT(11)     NOT NULL,
  `votingId`  INT(11)     NOT NULL,
  `opinionId` INT(11)     NOT NULL,
  `userId`    INT(11)     NOT NULL,
  `date`      INT(11)     NOT NULL,
  `IP`        VARCHAR(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vote` (`id`, `votingId`, `opinionId`, `userId`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `pn_voting` (
  `id`       INT(11) NOT NULL AUTO_INCREMENT,
  `parentId` INT(11) DEFAULT NULL,
  `title`    VARCHAR(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;