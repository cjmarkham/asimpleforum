CREATE TABLE IF NOT EXISTS `profile_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `comment` text NOT NULL,
  `added` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;