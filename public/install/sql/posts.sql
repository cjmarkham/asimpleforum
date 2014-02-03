CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic` int(11) NOT NULL,
  `forum` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `poster` int(11) NOT NULL,
  `added` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  `edits` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `topic` (`topic`,`forum`,`poster`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;