-- phpMyAdmin SQL Dump
-- version 4.2.8
-- http://www.phpmyadmin.net
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Stř 12. lis 2014, 22:02
-- Verze serveru: 5.6.12-log
-- Verze PHP: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databáze: `fcz-v2`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `Access`
--

CREATE TABLE IF NOT EXISTS `Access` (
  `ContentId` int(10) unsigned NOT NULL COMMENT 'FK, compound PK',
  `UserId` int(11) unsigned NOT NULL COMMENT 'FK, compound PK',
  `PermissionId` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `BookmarkCategories`
--

CREATE TABLE IF NOT EXISTS `BookmarkCategories` (
`Id` int(11) unsigned NOT NULL COMMENT 'PK',
  `UserId` int(11) unsigned NOT NULL COMMENT 'FK',
  `Name` varchar(100) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `Bookmarks`
--

CREATE TABLE IF NOT EXISTS `Bookmarks` (
  `UserId` int(11) unsigned NOT NULL COMMENT 'FK, compound PK',
  `TopicId` int(11) unsigned NOT NULL COMMENT 'FK, compound PK',
  `CategoryId` int(11) unsigned DEFAULT NULL COMMENT 'FK'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `CalendarReminders`
--

CREATE TABLE IF NOT EXISTS `CalendarReminders` (
`Id` int(10) unsigned NOT NULL COMMENT 'PK',
  `UserId` int(10) unsigned NOT NULL COMMENT 'FK',
  `Time` datetime NOT NULL,
  `Name` varchar(25) COLLATE utf8_czech_ci NOT NULL,
  `Description` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL,
  `CalendarLabel` varchar(10) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `CmsPages`
--

CREATE TABLE IF NOT EXISTS `CmsPages` (
`Id` int(10) unsigned NOT NULL COMMENT 'PK',
  `ContentId` int(10) unsigned DEFAULT NULL COMMENT 'FK',
  `Name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `Description` tinytext COLLATE utf8_czech_ci,
  `Text` mediumtext COLLATE utf8_czech_ci NOT NULL,
  `Alias` varchar(25) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'URL alias'
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `Contacts`
--

CREATE TABLE IF NOT EXISTS `Contacts` (
`Id` int(10) unsigned NOT NULL COMMENT 'PK',
  `UserId` int(10) unsigned NOT NULL COMMENT 'FK',
  `TypeId` int(10) unsigned DEFAULT NULL COMMENT 'FK - Contact type Id. NULL = user defined type',
  `Name` varchar(25) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'For user defined types',
  `Value` varchar(50) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `ContactTypes`
--

CREATE TABLE IF NOT EXISTS `ContactTypes` (
`Id` int(11) unsigned NOT NULL COMMENT 'PK',
  `Name` varchar(25) COLLATE utf8_czech_ci NOT NULL,
  `Url` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `Content`
--

CREATE TABLE IF NOT EXISTS `Content` (
`Id` int(10) unsigned NOT NULL COMMENT 'PK',
  `Type` enum('Topic','Image','CMS','Writing','Event') COLLATE utf8_czech_ci NOT NULL,
  `TimeCreated` datetime NOT NULL,
  `Deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `DefaultPermissions` int(10) unsigned NOT NULL COMMENT 'FK - Permission Id',
  `LastModifiedTime` datetime NOT NULL,
  `LastModifiedByUser` int(10) unsigned DEFAULT NULL COMMENT 'FK - User Id',
  `IsForRegisteredOnly` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `IsForAdultsOnly` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `IsDiscussionAllowed` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `IsRatingAllowed` tinyint(1) unsigned NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `EditedPostHistory`
--

CREATE TABLE IF NOT EXISTS `EditedPostHistory` (
`Id` int(10) unsigned NOT NULL COMMENT 'PK',
  `EditedPostId` int(10) unsigned NOT NULL COMMENT 'FK',
  `Text` text COLLATE utf8_czech_ci NOT NULL,
  `TimeEdited` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `EventAttendances`
--

CREATE TABLE IF NOT EXISTS `EventAttendances` (
  `EventId` int(10) unsigned NOT NULL COMMENT 'FK, compound PK',
  `UserId` int(10) unsigned NOT NULL COMMENT 'FK, compound PK',
  `Attending` enum('Yes','No','Maybe') COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `Events`
--

CREATE TABLE IF NOT EXISTS `Events` (
`Id` int(10) unsigned NOT NULL COMMENT 'PK',
  `ContentId` int(10) unsigned NOT NULL COMMENT 'FK',
  `Name` varchar(25) COLLATE utf8_czech_ci NOT NULL,
  `Description` tinytext COLLATE utf8_czech_ci,
  `StartTime` datetime DEFAULT NULL,
  `EndTime` datetime DEFAULT NULL,
  `CalendarLabel` varchar(10) COLLATE utf8_czech_ci NOT NULL,
  `Header` int(10) unsigned DEFAULT NULL COMMENT 'FK - CMS page id',
  `Capacity` int(20) NOT NULL,
  `Place` varchar(500) COLLATE utf8_czech_ci NOT NULL,
  `GPS` varchar(100) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `ForumCategoryPresets`
--

CREATE TABLE IF NOT EXISTS `ForumCategoryPresets` (
  `TopicCategoryId` int(10) unsigned NOT NULL COMMENT 'FK, compound PK',
  `UserId` int(10) unsigned NOT NULL COMMENT 'FK, compound PK',
  `Hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Toggles visibility of forum category'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `ForumTopicPresets`
--

CREATE TABLE IF NOT EXISTS `ForumTopicPresets` (
  `TopicId` int(11) unsigned NOT NULL COMMENT 'FK, compound PK',
  `UserId` int(11) unsigned NOT NULL COMMENT 'FK, compound PK',
  `Uninteresting` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Marks topic as uninteresting'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `Ignorelist`
--

CREATE TABLE IF NOT EXISTS `Ignorelist` (
  `IgnoringUserId` int(10) unsigned NOT NULL COMMENT 'FK, compound PK',
  `IgnoredUserId` int(10) unsigned NOT NULL COMMENT 'FK, compound PK',
  `IgnoreType` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `ImageExpositions`
--

CREATE TABLE IF NOT EXISTS `ImageExpositions` (
`Id` int(11) unsigned NOT NULL COMMENT 'PK',
  `Name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `Owner` int(11) unsigned NOT NULL COMMENT 'FK - User Id',
  `Description` tinytext COLLATE utf8_czech_ci,
  `Thumbnail` int(11) unsigned DEFAULT NULL COMMENT 'FK - Uploaded file Id',
  `Presentation` int(11) unsigned DEFAULT NULL COMMENT 'FK - CMS page Id'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `Images`
--

CREATE TABLE IF NOT EXISTS `Images` (
`Id` int(11) unsigned NOT NULL COMMENT 'PK',
  `ContentId` int(11) unsigned NOT NULL COMMENT 'FK',
  `UploadedFileId` int(11) unsigned NOT NULL COMMENT 'FK',
  `Name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `Description` text COLLATE utf8_czech_ci NOT NULL,
  `Exposition` int(10) unsigned DEFAULT NULL COMMENT 'FK'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `LastVisits`
--

CREATE TABLE IF NOT EXISTS `LastVisits` (
  `ContentId` int(10) unsigned NOT NULL COMMENT 'FK, compound PK',
  `UserId` int(10) unsigned NOT NULL COMMENT 'FK, compound PK',
  `Time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `Notifications`
--

CREATE TABLE IF NOT EXISTS `Notifications` (
`Id` int(11) NOT NULL,
  `Parent` varchar(100) COLLATE utf8_bin NOT NULL,
  `Text` varchar(150) COLLATE utf8_bin NOT NULL,
  `Time` datetime NOT NULL,
  `Href` varchar(100) COLLATE utf8_bin NOT NULL,
  `Image` varchar(200) COLLATE utf8_bin NOT NULL,
  `IsNotifed` tinyint(1) NOT NULL,
  `IsView` tinyint(1) NOT NULL,
  `UserId` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabulky `Ownership`
--

CREATE TABLE IF NOT EXISTS `Ownership` (
  `ContentId` int(10) unsigned NOT NULL COMMENT 'FK, compound PK',
  `UserId` int(11) unsigned NOT NULL COMMENT 'FK, compound PK'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `Permissions`
--

CREATE TABLE IF NOT EXISTS `Permissions` (
`Id` int(10) unsigned NOT NULL COMMENT 'PK',
  `CanListContent` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `CanViewContent` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `CanEditContentAndAttributes` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `CanEditHeader` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `CanEditOwnPosts` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `CanDeleteOwnPosts` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `CanReadPosts` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `CanDeletePosts` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `CanWritePosts` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `CanEditPermissions` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `CanEditPolls` tinyint(1) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `PollAnswers`
--

CREATE TABLE IF NOT EXISTS `PollAnswers` (
`Id` int(11) unsigned NOT NULL COMMENT 'PK',
  `PollId` int(11) unsigned NOT NULL COMMENT 'FK',
  `Text` tinytext COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `Polls`
--

CREATE TABLE IF NOT EXISTS `Polls` (
`Id` int(11) unsigned NOT NULL COMMENT 'PK',
  `ContentId` int(11) unsigned NOT NULL COMMENT 'FK',
  `Name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `Description` tinytext COLLATE utf8_czech_ci NOT NULL,
  `MaxVotesPerUser` int(11) DEFAULT NULL,
  `SaveIndividualVotes` tinyint(1) unsigned NOT NULL,
  `DisplayVotersTo` enum('Nobody,','OtherVoters,','Everybody') COLLATE utf8_czech_ci NOT NULL COMMENT 'Controls who can see voter names',
  `AllowCustomAnswer` tinyint(11) NOT NULL,
  `ShowInTopic` tinyint(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `PollVotes`
--

CREATE TABLE IF NOT EXISTS `PollVotes` (
`Id` int(11) unsigned NOT NULL COMMENT 'PK',
  `PollId` int(11) unsigned NOT NULL COMMENT 'FK',
  `AnswerId` int(11) unsigned NOT NULL COMMENT 'FK',
  `UserId` int(11) unsigned NOT NULL COMMENT 'FK'
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `Posts`
--

CREATE TABLE IF NOT EXISTS `Posts` (
`Id` int(10) unsigned NOT NULL COMMENT 'PK',
  `ContentId` int(10) unsigned NOT NULL COMMENT 'FK',
  `Author` int(10) unsigned NOT NULL COMMENT 'FK - User Id',
  `Text` text COLLATE utf8_czech_ci NOT NULL,
  `TimeCreated` datetime NOT NULL,
  `Deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `Edited` varchar(200) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `PrivateMessages`
--

CREATE TABLE IF NOT EXISTS `PrivateMessages` (
`Id` int(11) unsigned NOT NULL COMMENT 'PK',
  `SenderId` int(11) unsigned NOT NULL COMMENT 'FK - user Id',
  `AddresseeId` int(11) unsigned NOT NULL COMMENT 'FK - user Id',
  `Text` text COLLATE utf8_czech_ci NOT NULL,
  `TimeSent` datetime NOT NULL,
  `Deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `Read` tinyint(1) unsigned NOT NULL,
  `ReadTime` datetime NOT NULL,
  `File` varchar(500) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `Ratings`
--

CREATE TABLE IF NOT EXISTS `Ratings` (
  `ContentId` int(10) unsigned NOT NULL COMMENT 'FK, compound PK',
  `UserId` int(10) unsigned NOT NULL COMMENT 'FK, compound PK',
  `Rating` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `RatingsPost`
--

CREATE TABLE IF NOT EXISTS `RatingsPost` (
  `ContentId` int(10) unsigned NOT NULL,
  `PostId` int(10) unsigned NOT NULL,
  `UserId` int(11) unsigned NOT NULL,
  `Rating` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabulky `TopicCategories`
--

CREATE TABLE IF NOT EXISTS `TopicCategories` (
`Id` int(11) unsigned NOT NULL COMMENT 'PK',
  `Name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `Description` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `Topics`
--

CREATE TABLE IF NOT EXISTS `Topics` (
`Id` int(11) unsigned NOT NULL COMMENT 'PK',
  `Name` tinytext COLLATE utf8_czech_ci NOT NULL,
  `ContentId` int(11) unsigned NOT NULL COMMENT 'FK',
  `CategoryId` int(11) unsigned DEFAULT NULL COMMENT 'FK',
  `Header` int(11) unsigned NOT NULL COMMENT 'FK - CMS page Id',
  `HeaderForDisallowedUsers` int(11) unsigned DEFAULT NULL COMMENT 'FK - CMS page Id',
  `IsFlame` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Marks topic as flamewar',
  `Pin` int(10) unsigned NOT NULL,
  `Lock` int(11) NOT NULL,
  `Delete` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `UploadedFiles`
--

CREATE TABLE IF NOT EXISTS `UploadedFiles` (
`Id` int(11) unsigned NOT NULL,
  `Key` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `FileName` tinytext COLLATE utf8_czech_ci NOT NULL COMMENT 'Server file path',
  `Name` varchar(50) COLLATE utf8_czech_ci NOT NULL COMMENT 'File description',
  `SourceType` enum('GalleryImage','CmsImage','CmsAttachment','ForumImage','ForumAttachment','SpecialCmsImage','SpecialCmsAttachment','ExpositionThumbnail') COLLATE utf8_czech_ci DEFAULT NULL,
  `SourceId` int(11) unsigned DEFAULT NULL COMMENT 'FK - Id of event/topic/cms etc. where the file was uploaded from.'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `Users`
--

CREATE TABLE IF NOT EXISTS `Users` (
`Id` int(11) unsigned NOT NULL COMMENT 'PK',
  `Username` varchar(50) COLLATE utf8_czech_ci NOT NULL COMMENT 'Login only',
  `Nickname` varchar(100) COLLATE utf8_czech_ci NOT NULL COMMENT 'Display name',
  `Password` tinytext COLLATE utf8_czech_ci NOT NULL,
  `Salt` tinytext COLLATE utf8_czech_ci NOT NULL,
  `OtherNicknames` tinytext COLLATE utf8_czech_ci COMMENT 'Furry',
  `Species` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'Furry',
  `FurrySex` enum('Male','Female','Herm','Sexless') COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'Furry',
  `ShortDescriptionForMembers` text COLLATE utf8_czech_ci COMMENT 'Furry',
  `ShortDescriptionForGuests` text COLLATE utf8_czech_ci COMMENT 'Furry',
  `ProfileForMembers` int(11) unsigned DEFAULT NULL COMMENT 'FK - CMS page id',
  `ProfileForGuests` int(11) unsigned DEFAULT NULL COMMENT 'FK - CMS page id',
  `ImageGalleryPresentation` int(11) unsigned DEFAULT NULL COMMENT 'FK - CMS page id',
  `WritingsPresentation` int(11) unsigned DEFAULT NULL COMMENT 'FK - CMS page id',
  `AvatarFilename` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'Furry',
  `FullName` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'Real',
  `Address` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'Real',
  `RealSex` enum('Male','Female') COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'Real',
  `DateOfBirth` date NOT NULL COMMENT 'Real',
  `FavouriteWebsites` text COLLATE utf8_czech_ci COMMENT 'Real',
  `ProfilePhotoFilename` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'Real',
  `Hobbies` text COLLATE utf8_czech_ci COMMENT 'Real',
  `GoogleMapsLink` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'Real',
  `DistanceFromPrague` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'Real',
  `WillingnessToTravel` enum('Small','Medium','Big') COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'Real',
  `Email` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'Real',
  `LastLogin` datetime DEFAULT NULL COMMENT 'Cache',
  `LastVisitedPage` varchar(25) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'Cache',
  `LastActivityTime` datetime DEFAULT NULL COMMENT 'Cache',
  `SendIntercomToMail` tinyint(1) unsigned DEFAULT '0' COMMENT 'Config',
  `PostsOrdering` enum('NewestOnTop','OldestOnTop') COLLATE utf8_czech_ci NOT NULL DEFAULT 'NewestOnTop' COMMENT 'Config',
  `PostsPerPage` int(10) unsigned NOT NULL DEFAULT '25' COMMENT 'Config',
  `IsAdmin` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Admin',
  `IsApproved` tinyint(1) unsigned DEFAULT '0' COMMENT 'Admin',
  `IsBanned` tinyint(1) unsigned DEFAULT '0' COMMENT 'Admin',
  `IsFrozen` tinyint(1) unsigned DEFAULT '0' COMMENT 'Admin',
  `Deleted` tinyint(1) unsigned DEFAULT '0' COMMENT 'Admin'
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `WritingCategories`
--

CREATE TABLE IF NOT EXISTS `WritingCategories` (
`Id` int(10) unsigned NOT NULL COMMENT 'PK',
  `Name` varchar(25) COLLATE utf8_czech_ci NOT NULL,
  `Description` tinytext COLLATE utf8_czech_ci,
  `IsForAdultsOnly` tinyint(1) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `Writings`
--

CREATE TABLE IF NOT EXISTS `Writings` (
`Id` int(10) unsigned NOT NULL COMMENT 'PK',
  `ContentId` int(10) unsigned NOT NULL COMMENT 'FK',
  `Name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `Description` tinytext COLLATE utf8_czech_ci,
  `Text` text COLLATE utf8_czech_ci NOT NULL,
  `CategoryId` int(10) unsigned DEFAULT NULL COMMENT 'FK'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `Access`
--
ALTER TABLE `Access`
 ADD PRIMARY KEY (`ContentId`,`UserId`), ADD KEY `UserId` (`UserId`), ADD KEY `PermissionId` (`PermissionId`);

--
-- Klíče pro tabulku `BookmarkCategories`
--
ALTER TABLE `BookmarkCategories`
 ADD PRIMARY KEY (`Id`), ADD KEY `UserId` (`UserId`);

--
-- Klíče pro tabulku `Bookmarks`
--
ALTER TABLE `Bookmarks`
 ADD PRIMARY KEY (`UserId`,`TopicId`), ADD KEY `CategoryId` (`CategoryId`);

--
-- Klíče pro tabulku `CalendarReminders`
--
ALTER TABLE `CalendarReminders`
 ADD PRIMARY KEY (`Id`), ADD KEY `UserId` (`UserId`);

--
-- Klíče pro tabulku `CmsPages`
--
ALTER TABLE `CmsPages`
 ADD PRIMARY KEY (`Id`), ADD KEY `Alias` (`Alias`), ADD KEY `ContentId` (`ContentId`);

--
-- Klíče pro tabulku `Contacts`
--
ALTER TABLE `Contacts`
 ADD PRIMARY KEY (`Id`), ADD KEY `UserId` (`UserId`), ADD KEY `TypeId` (`TypeId`);

--
-- Klíče pro tabulku `ContactTypes`
--
ALTER TABLE `ContactTypes`
 ADD PRIMARY KEY (`Id`);

--
-- Klíče pro tabulku `Content`
--
ALTER TABLE `Content`
 ADD PRIMARY KEY (`Id`), ADD KEY `LastModifiedByUser` (`LastModifiedByUser`), ADD KEY `DefaultPermissions` (`DefaultPermissions`);

--
-- Klíče pro tabulku `EditedPostHistory`
--
ALTER TABLE `EditedPostHistory`
 ADD PRIMARY KEY (`Id`), ADD KEY `EditedPostId` (`EditedPostId`);

--
-- Klíče pro tabulku `EventAttendances`
--
ALTER TABLE `EventAttendances`
 ADD PRIMARY KEY (`EventId`,`UserId`), ADD KEY `UserId` (`UserId`);

--
-- Klíče pro tabulku `Events`
--
ALTER TABLE `Events`
 ADD PRIMARY KEY (`Id`), ADD KEY `ContentId` (`ContentId`), ADD KEY `Header` (`Header`);

--
-- Klíče pro tabulku `ForumCategoryPresets`
--
ALTER TABLE `ForumCategoryPresets`
 ADD PRIMARY KEY (`TopicCategoryId`,`UserId`), ADD KEY `UserId` (`UserId`);

--
-- Klíče pro tabulku `ForumTopicPresets`
--
ALTER TABLE `ForumTopicPresets`
 ADD PRIMARY KEY (`TopicId`,`UserId`), ADD KEY `UserId` (`UserId`);

--
-- Klíče pro tabulku `Ignorelist`
--
ALTER TABLE `Ignorelist`
 ADD PRIMARY KEY (`IgnoringUserId`,`IgnoredUserId`), ADD KEY `IgnoredUserId` (`IgnoredUserId`);

--
-- Klíče pro tabulku `ImageExpositions`
--
ALTER TABLE `ImageExpositions`
 ADD PRIMARY KEY (`Id`), ADD KEY `Thumbnail` (`Thumbnail`), ADD KEY `Presentation` (`Presentation`), ADD KEY `Owner` (`Owner`);

--
-- Klíče pro tabulku `Images`
--
ALTER TABLE `Images`
 ADD PRIMARY KEY (`Id`), ADD KEY `ContentId` (`ContentId`), ADD KEY `UploadedFileId` (`UploadedFileId`);

--
-- Klíče pro tabulku `LastVisits`
--
ALTER TABLE `LastVisits`
 ADD PRIMARY KEY (`ContentId`,`UserId`), ADD KEY `UserId` (`UserId`);

--
-- Klíče pro tabulku `Notifications`
--
ALTER TABLE `Notifications`
 ADD PRIMARY KEY (`Id`);

--
-- Klíče pro tabulku `Ownership`
--
ALTER TABLE `Ownership`
 ADD PRIMARY KEY (`ContentId`,`UserId`), ADD KEY `UserId` (`UserId`);

--
-- Klíče pro tabulku `Permissions`
--
ALTER TABLE `Permissions`
 ADD PRIMARY KEY (`Id`);

--
-- Klíče pro tabulku `PollAnswers`
--
ALTER TABLE `PollAnswers`
 ADD PRIMARY KEY (`Id`), ADD KEY `PollId` (`PollId`);

--
-- Klíče pro tabulku `Polls`
--
ALTER TABLE `Polls`
 ADD PRIMARY KEY (`Id`), ADD KEY `ContentId` (`ContentId`);

--
-- Klíče pro tabulku `PollVotes`
--
ALTER TABLE `PollVotes`
 ADD PRIMARY KEY (`Id`), ADD KEY `PollId` (`PollId`), ADD KEY `AnswerId` (`AnswerId`), ADD KEY `UserId` (`UserId`);

--
-- Klíče pro tabulku `Posts`
--
ALTER TABLE `Posts`
 ADD PRIMARY KEY (`Id`), ADD KEY `ContentId` (`ContentId`), ADD KEY `Author` (`Author`);

--
-- Klíče pro tabulku `PrivateMessages`
--
ALTER TABLE `PrivateMessages`
 ADD PRIMARY KEY (`Id`), ADD KEY `SenderId` (`SenderId`), ADD KEY `AddresseeId` (`AddresseeId`);

--
-- Klíče pro tabulku `Ratings`
--
ALTER TABLE `Ratings`
 ADD PRIMARY KEY (`ContentId`,`UserId`), ADD KEY `UserId` (`UserId`);

--
-- Klíče pro tabulku `RatingsPost`
--
ALTER TABLE `RatingsPost`
 ADD KEY `PostId` (`PostId`), ADD KEY `UserId` (`UserId`), ADD KEY `ContentId` (`ContentId`);

--
-- Klíče pro tabulku `TopicCategories`
--
ALTER TABLE `TopicCategories`
 ADD PRIMARY KEY (`Id`);

--
-- Klíče pro tabulku `Topics`
--
ALTER TABLE `Topics`
 ADD PRIMARY KEY (`Id`), ADD KEY `ContentId` (`ContentId`), ADD KEY `CategoryId` (`CategoryId`), ADD KEY `Header` (`Header`), ADD KEY `HeaderForDisallowedUsers` (`HeaderForDisallowedUsers`);

--
-- Klíče pro tabulku `UploadedFiles`
--
ALTER TABLE `UploadedFiles`
 ADD PRIMARY KEY (`Id`);

--
-- Klíče pro tabulku `Users`
--
ALTER TABLE `Users`
 ADD PRIMARY KEY (`Id`), ADD KEY `ImageGalleryPresentation` (`ImageGalleryPresentation`), ADD KEY `ProfileForGuests` (`ProfileForGuests`), ADD KEY `WritingsPresentation` (`WritingsPresentation`), ADD KEY `ProfileForMembers` (`ProfileForMembers`);

--
-- Klíče pro tabulku `WritingCategories`
--
ALTER TABLE `WritingCategories`
 ADD PRIMARY KEY (`Id`);

--
-- Klíče pro tabulku `Writings`
--
ALTER TABLE `Writings`
 ADD PRIMARY KEY (`Id`), ADD KEY `ContentId` (`ContentId`), ADD KEY `CategoryId` (`CategoryId`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `BookmarkCategories`
--
ALTER TABLE `BookmarkCategories`
MODIFY `Id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK';
--
-- AUTO_INCREMENT pro tabulku `CalendarReminders`
--
ALTER TABLE `CalendarReminders`
MODIFY `Id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK';
--
-- AUTO_INCREMENT pro tabulku `CmsPages`
--
ALTER TABLE `CmsPages`
MODIFY `Id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT pro tabulku `Contacts`
--
ALTER TABLE `Contacts`
MODIFY `Id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK';
--
-- AUTO_INCREMENT pro tabulku `ContactTypes`
--
ALTER TABLE `ContactTypes`
MODIFY `Id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK';
--
-- AUTO_INCREMENT pro tabulku `Content`
--
ALTER TABLE `Content`
MODIFY `Id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT pro tabulku `EditedPostHistory`
--
ALTER TABLE `EditedPostHistory`
MODIFY `Id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK';
--
-- AUTO_INCREMENT pro tabulku `Events`
--
ALTER TABLE `Events`
MODIFY `Id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT pro tabulku `ImageExpositions`
--
ALTER TABLE `ImageExpositions`
MODIFY `Id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pro tabulku `Images`
--
ALTER TABLE `Images`
MODIFY `Id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pro tabulku `Notifications`
--
ALTER TABLE `Notifications`
MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT pro tabulku `Permissions`
--
ALTER TABLE `Permissions`
MODIFY `Id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT pro tabulku `PollAnswers`
--
ALTER TABLE `PollAnswers`
MODIFY `Id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT pro tabulku `Polls`
--
ALTER TABLE `Polls`
MODIFY `Id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT pro tabulku `PollVotes`
--
ALTER TABLE `PollVotes`
MODIFY `Id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT pro tabulku `Posts`
--
ALTER TABLE `Posts`
MODIFY `Id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',AUTO_INCREMENT=63;
--
-- AUTO_INCREMENT pro tabulku `PrivateMessages`
--
ALTER TABLE `PrivateMessages`
MODIFY `Id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT pro tabulku `TopicCategories`
--
ALTER TABLE `TopicCategories`
MODIFY `Id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK';
--
-- AUTO_INCREMENT pro tabulku `Topics`
--
ALTER TABLE `Topics`
MODIFY `Id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT pro tabulku `UploadedFiles`
--
ALTER TABLE `UploadedFiles`
MODIFY `Id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pro tabulku `Users`
--
ALTER TABLE `Users`
MODIFY `Id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT pro tabulku `WritingCategories`
--
ALTER TABLE `WritingCategories`
MODIFY `Id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pro tabulku `Writings`
--
ALTER TABLE `Writings`
MODIFY `Id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',AUTO_INCREMENT=2;
--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `Access`
--
ALTER TABLE `Access`
ADD CONSTRAINT `Access_ibfk_1` FOREIGN KEY (`ContentId`) REFERENCES `Content` (`Id`) ON DELETE NO ACTION,
ADD CONSTRAINT `Access_ibfk_2` FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION,
ADD CONSTRAINT `Access_ibfk_3` FOREIGN KEY (`PermissionId`) REFERENCES `Permissions` (`Id`) ON DELETE NO ACTION;

--
-- Omezení pro tabulku `BookmarkCategories`
--
ALTER TABLE `BookmarkCategories`
ADD CONSTRAINT `BookmarkCategories_ibfk_1` FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `Bookmarks`
--
ALTER TABLE `Bookmarks`
ADD CONSTRAINT `Bookmarks_ibfk_1` FOREIGN KEY (`CategoryId`) REFERENCES `BookmarkCategories` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `CalendarReminders`
--
ALTER TABLE `CalendarReminders`
ADD CONSTRAINT `CalendarReminders_ibfk_1` FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `CmsPages`
--
ALTER TABLE `CmsPages`
ADD CONSTRAINT `CmsPages_ibfk_3` FOREIGN KEY (`ContentId`) REFERENCES `Content` (`Id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `Contacts`
--
ALTER TABLE `Contacts`
ADD CONSTRAINT `Contacts_ibfk_1` FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `Contacts_ibfk_2` FOREIGN KEY (`TypeId`) REFERENCES `ContactTypes` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `Content`
--
ALTER TABLE `Content`
ADD CONSTRAINT `Content_ibfk_1` FOREIGN KEY (`LastModifiedByUser`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `Content_ibfk_3` FOREIGN KEY (`DefaultPermissions`) REFERENCES `Permissions` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `EditedPostHistory`
--
ALTER TABLE `EditedPostHistory`
ADD CONSTRAINT `EditedPostHistory_ibfk_1` FOREIGN KEY (`EditedPostId`) REFERENCES `EditedPostHistory` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `EventAttendances`
--
ALTER TABLE `EventAttendances`
ADD CONSTRAINT `EventAttendances_ibfk_1` FOREIGN KEY (`EventId`) REFERENCES `Events` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `EventAttendances_ibfk_2` FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `Events`
--
ALTER TABLE `Events`
ADD CONSTRAINT `Events_ibfk_1` FOREIGN KEY (`ContentId`) REFERENCES `Content` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `Events_ibfk_2` FOREIGN KEY (`Header`) REFERENCES `CmsPages` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `ForumCategoryPresets`
--
ALTER TABLE `ForumCategoryPresets`
ADD CONSTRAINT `ForumCategoryPresets_ibfk_1` FOREIGN KEY (`TopicCategoryId`) REFERENCES `TopicCategories` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `ForumCategoryPresets_ibfk_2` FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `ForumTopicPresets`
--
ALTER TABLE `ForumTopicPresets`
ADD CONSTRAINT `ForumTopicPresets_ibfk_1` FOREIGN KEY (`TopicId`) REFERENCES `Topics` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `ForumTopicPresets_ibfk_2` FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `Ignorelist`
--
ALTER TABLE `Ignorelist`
ADD CONSTRAINT `Ignorelist_ibfk_1` FOREIGN KEY (`IgnoringUserId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `Ignorelist_ibfk_2` FOREIGN KEY (`IgnoredUserId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `ImageExpositions`
--
ALTER TABLE `ImageExpositions`
ADD CONSTRAINT `ImageExpositions_ibfk_1` FOREIGN KEY (`Thumbnail`) REFERENCES `UploadedFiles` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `ImageExpositions_ibfk_2` FOREIGN KEY (`Presentation`) REFERENCES `CmsPages` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `ImageExpositions_ibfk_3` FOREIGN KEY (`Owner`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `Images`
--
ALTER TABLE `Images`
ADD CONSTRAINT `Images_ibfk_1` FOREIGN KEY (`ContentId`) REFERENCES `Content` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `Images_ibfk_2` FOREIGN KEY (`UploadedFileId`) REFERENCES `UploadedFiles` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `LastVisits`
--
ALTER TABLE `LastVisits`
ADD CONSTRAINT `LastVisits_ibfk_1` FOREIGN KEY (`ContentId`) REFERENCES `Content` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `LastVisits_ibfk_2` FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `Ownership`
--
ALTER TABLE `Ownership`
ADD CONSTRAINT `Ownership_ibfk_1` FOREIGN KEY (`ContentId`) REFERENCES `Content` (`Id`) ON DELETE NO ACTION,
ADD CONSTRAINT `Ownership_ibfk_2` FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION;

--
-- Omezení pro tabulku `PollAnswers`
--
ALTER TABLE `PollAnswers`
ADD CONSTRAINT `PollAnswers_ibfk_1` FOREIGN KEY (`PollId`) REFERENCES `Polls` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `Polls`
--
ALTER TABLE `Polls`
ADD CONSTRAINT `Polls_ibfk_1` FOREIGN KEY (`ContentId`) REFERENCES `Content` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `PollVotes`
--
ALTER TABLE `PollVotes`
ADD CONSTRAINT `PollVotes_ibfk_1` FOREIGN KEY (`PollId`) REFERENCES `Polls` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `PollVotes_ibfk_2` FOREIGN KEY (`AnswerId`) REFERENCES `PollAnswers` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `PollVotes_ibfk_3` FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `Posts`
--
ALTER TABLE `Posts`
ADD CONSTRAINT `Posts_ibfk_1` FOREIGN KEY (`ContentId`) REFERENCES `Content` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `Posts_ibfk_2` FOREIGN KEY (`Author`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `PrivateMessages`
--
ALTER TABLE `PrivateMessages`
ADD CONSTRAINT `PrivateMessages_ibfk_1` FOREIGN KEY (`SenderId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `PrivateMessages_ibfk_2` FOREIGN KEY (`AddresseeId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `Ratings`
--
ALTER TABLE `Ratings`
ADD CONSTRAINT `Ratings_ibfk_1` FOREIGN KEY (`ContentId`) REFERENCES `Content` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `Ratings_ibfk_2` FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `RatingsPost`
--
ALTER TABLE `RatingsPost`
ADD CONSTRAINT `ratingspost_ibfk_1` FOREIGN KEY (`PostId`) REFERENCES `Posts` (`Id`),
ADD CONSTRAINT `ratingspost_ibfk_2` FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`),
ADD CONSTRAINT `ratingspost_ibfk_3` FOREIGN KEY (`ContentId`) REFERENCES `Content` (`Id`);

--
-- Omezení pro tabulku `Topics`
--
ALTER TABLE `Topics`
ADD CONSTRAINT `Topics_ibfk_1` FOREIGN KEY (`ContentId`) REFERENCES `content` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `Topics_ibfk_2` FOREIGN KEY (`CategoryId`) REFERENCES `topiccategories` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `Topics_ibfk_3` FOREIGN KEY (`Header`) REFERENCES `cmspages` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `Topics_ibfk_4` FOREIGN KEY (`HeaderForDisallowedUsers`) REFERENCES `cmspages` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `Users`
--
ALTER TABLE `Users`
ADD CONSTRAINT `Users_ibfk_10` FOREIGN KEY (`ProfileForGuests`) REFERENCES `CmsPages` (`Id`) ON DELETE SET NULL ON UPDATE NO ACTION,
ADD CONSTRAINT `Users_ibfk_11` FOREIGN KEY (`WritingsPresentation`) REFERENCES `CmsPages` (`Id`) ON DELETE SET NULL ON UPDATE NO ACTION,
ADD CONSTRAINT `Users_ibfk_13` FOREIGN KEY (`ProfileForMembers`) REFERENCES `CmsPages` (`Id`) ON DELETE SET NULL ON UPDATE NO ACTION,
ADD CONSTRAINT `Users_ibfk_9` FOREIGN KEY (`ImageGalleryPresentation`) REFERENCES `CmsPages` (`Id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `Writings`
--
ALTER TABLE `Writings`
ADD CONSTRAINT `Writings_ibfk_1` FOREIGN KEY (`ContentId`) REFERENCES `Content` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `Writings_ibfk_2` FOREIGN KEY (`CategoryId`) REFERENCES `WritingCategories` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
