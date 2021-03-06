CREATE TABLE `EventAttendances` (
  `EventId` int(10) unsigned NOT NULL COMMENT 'FK, compound PK',
  `UserId` int(10) unsigned NOT NULL COMMENT 'FK, compound PK',
  `Attending` enum('Yes','No','Maybe') COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`EventId`,`UserId`),
  KEY `UserId` (`UserId`),
  CONSTRAINT `EventAttendances_ibfk_4` FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `EventAttendances_ibfk_3` FOREIGN KEY (`EventId`) REFERENCES `Events` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci