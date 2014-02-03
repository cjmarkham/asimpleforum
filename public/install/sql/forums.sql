CREATE TABLE IF NOT EXISTS `forums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL,
  `display` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `left` int(11) NOT NULL,
  `right` int(11) NOT NULL,
  `description` text NOT NULL,
  `topics` int(11) NOT NULL DEFAULT '0',
  `posts` int(11) NOT NULL DEFAULT '0',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `lastTopicId` int(11) NOT NULL,
  `lastPosterId` int(11) NOT NULL,
  `lastPostTime` int(11) NOT NULL,
  `lastPostId` int(11) NOT NULL,
  `added` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;