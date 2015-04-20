-- Create syntax for TABLE 'party_songs'
CREATE TABLE `party_songs` (
  `TrackID` bigint(20) unsigned NOT NULL,
  `Name` varchar(200) DEFAULT NULL,
  `Artist` varchar(200) DEFAULT NULL,
  `Composer` varchar(200) DEFAULT NULL,
  `AlbumArtist` varchar(200) DEFAULT NULL,
  `Album` varchar(200) DEFAULT NULL,
  `Genre` varchar(200) DEFAULT NULL,
  `DiscNumber` int(11) DEFAULT NULL,
  `DiscCount` int(11) DEFAULT NULL,
  `TrackNumber` int(11) DEFAULT NULL,
  `TrackCount` int(11) DEFAULT NULL,
  `Year` int(11) DEFAULT NULL,
  `ArtworkCount` int(11) DEFAULT NULL,
  `Location` varchar(400) DEFAULT NULL,
  `hasCover` tinyint(4) NOT NULL DEFAULT '0',
  `duration` bigint(20) NOT NULL DEFAULT '0' COMMENT 'in seconds',
  PRIMARY KEY (`TrackID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'party_songs_queue'
CREATE TABLE `party_songs_queue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `upvote` int(11) NOT NULL DEFAULT '0',
  `TrackID` bigint(20) unsigned DEFAULT NULL,
  `by_nickname` varchar(200) DEFAULT NULL,
  `by_session_id` varchar(200) DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `running` datetime DEFAULT NULL,
  `played` datetime DEFAULT NULL,
  `twitter_done` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8;