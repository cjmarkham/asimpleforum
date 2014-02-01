--
-- Table structure for table `alerts`
--

CREATE TABLE IF NOT EXISTS `alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) DEFAULT NULL,
  `text` varchar(255) NOT NULL,
  `starts` int(11) NOT NULL,
  `expires` int(11) DEFAULT NULL,
  `pages` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `followers`
--

CREATE TABLE IF NOT EXISTS `followers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `following` int(11) NOT NULL,
  `added` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `forums`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=49 ;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(150) NOT NULL,
  `permission` int(11) NOT NULL,
  `added` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `permission` (`permission`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `default`, `name`, `permission`, `added`) VALUES
(1, 0, 'Admin', 127, 1388489226),
(2, 0, 'Moderator', 15, 1388489226),
(3, 1, 'Guest', 3, 1388489226);


-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE IF NOT EXISTS `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `postId` int(11) NOT NULL,
  `username` varchar(150) NOT NULL,
  `added` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `postId` (`postId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=78 ;

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE IF NOT EXISTS `profiles` (
  `id` int(11) NOT NULL,
  `first_name` varchar(150) DEFAULT 'No name',
  `last_name` varchar(150) DEFAULT 'given',
  `location` varchar(250) DEFAULT 'Unknown',
  `dob_day` int(11) DEFAULT NULL,
  `dob_month` int(11) DEFAULT NULL,
  `dob_year` int(11) DEFAULT NULL,
  `gender` enum('MALE','FEMALE','UNSPECIFIED') DEFAULT 'UNSPECIFIED',
  `views` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  KEY `location` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `profile_comments`
--

CREATE TABLE IF NOT EXISTS `profile_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `comment` text NOT NULL,
  `added` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=35 ;

-- --------------------------------------------------------

--
-- Table structure for table `profile_comment_likes`
--

CREATE TABLE IF NOT EXISTS `profile_comment_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_comment` int(11) NOT NULL,
  `username` varchar(150) NOT NULL,
  `added` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('POST','TOPIC','USER') NOT NULL,
  `typeId` int(11) NOT NULL,
  `reporter` int(11) NOT NULL,
  `reason` text NOT NULL,
  `ip` varchar(50) NOT NULL,
  `added` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  `userId` int(11) NOT NULL,
  `userAgent` text NOT NULL,
  `active` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL,
  `date_format` text NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=77 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(150) NOT NULL,
  `ip` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `perm_group` int(11) NOT NULL DEFAULT '3',
  `topics` int(11) NOT NULL DEFAULT '0',
  `posts` int(11) NOT NULL DEFAULT '0',
  `regdate` int(11) NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `lastActive` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `approved` (`approved`,`lastActive`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;