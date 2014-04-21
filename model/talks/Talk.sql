CREATE TABLE `pn_talks` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `createTime` DATETIME     NOT NULL,
  `owner`      INT          NOT NULL,
  `title`      VARCHAR(200) NOT NULL,
  `content`    TEXT,
  `rating`     INT          NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;