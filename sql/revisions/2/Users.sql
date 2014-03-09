ALTER TABLE `Users`
DROP FOREIGN KEY `Users_ibfk_10`,
ADD FOREIGN KEY (`ProfileForGuests`) REFERENCES `CmsPages` (`Id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `Users`
DROP FOREIGN KEY `Users_ibfk_11`,
ADD FOREIGN KEY (`WritingsPresentation`) REFERENCES `CmsPages` (`Id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `Users`
DROP FOREIGN KEY `Users_ibfk_13`,
ADD FOREIGN KEY (`ProfileForMembers`) REFERENCES `CmsPages` (`Id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `Users`
DROP FOREIGN KEY `Users_ibfk_9`,
ADD FOREIGN KEY (`ImageGalleryPresentation`) REFERENCES `CmsPages` (`Id`) ON DELETE SET NULL ON UPDATE NO ACTION;