CREATE TABLE IF NOT EXISTS `topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forum` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `poster` int(11) NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `replies` int(11) NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  `lastPostId` int(11) NOT NULL,
  `lastPosterId` int(11) NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `sticky` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `forum` (`forum`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;