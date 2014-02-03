CREATE TABLE IF NOT EXISTS `profile_comment_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_comment` int(11) NOT NULL,
  `username` varchar(150) NOT NULL,
  `added` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;