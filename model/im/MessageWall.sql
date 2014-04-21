CREATE TABLE `pn_messages_wall` (
  `sentTime` DATETIME NOT NULL,
  `author` int NOT NULL,
  `recipient` int NOT NULL,
  `content` text,
  `read` tinyint(1) NOT NULL,
  `removedAuthor` tinyint(1) NOT NULL,
  `removedRecipient` tinyint(1) NOT NULL,
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;