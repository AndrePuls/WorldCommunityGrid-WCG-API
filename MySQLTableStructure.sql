CREATE TABLE `resultStatus` (
  `ID` int(10) UNSIGNED NOT NULL,
  `AppName` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ModTime_String` datetime DEFAULT NULL,
  `DeviceId` mediumint(8) UNSIGNED DEFAULT NULL,
  `DeviceName` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ElapsedTime` decimal(17,14) DEFAULT NULL,
  `CpuTime` decimal(17,14) DEFAULT NULL,
  `ClaimedCredit` decimal(18,13) DEFAULT NULL,
  `GrantedCredit` decimal(18,13) DEFAULT NULL,
  `Outcome` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Return results based on the outcome of their processing.\r\n1 means success\r\n3 means error\r\n4 means no reply\r\n6 means validation error\r\n7 means abandoned',
  `ServerState` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Return results based on whether they are currently in progress or have already been reported back to World Community Grid.\r\n4 would return in-progress results\r\n5 would return results which have already been reported back to the server',
  `ValidateState` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Return results based on the validation status.\r\n0 means pending validation\r\n1 means valid\r\n2 means invalid\r\n4 means pending verification\r\n5 means results failed to validate within given deadline',
  `FileDeleteState` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Return results based on their file delete state. \r\n0 means not deleted\r\n1 means ready to delete\r\n2 means deleted',
  `ExitStatus` tinyint(3) UNSIGNED DEFAULT NULL,
  `Name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `SentTime` datetime DEFAULT NULL,
  `ReceivedTime` datetime DEFAULT NULL,
  `ReportDeadline` datetime DEFAULT NULL,
  `WorkunitId` int(8) UNSIGNED DEFAULT NULL,
  `ResultId` int(8) UNSIGNED DEFAULT NULL,
  `ModTime` int(8) UNSIGNED DEFAULT NULL,
  `LastChange` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `resultStatus`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Name` (`Name`),
  ADD UNIQUE KEY `WorkunitId` (`WorkunitId`),
  ADD UNIQUE KEY `ResultId` (`ResultId`),
  ADD KEY `DeviceId` (`DeviceId`),
  ADD KEY `DeviceName` (`DeviceName`),
  ADD KEY `Outcome` (`Outcome`),
  ADD KEY `ValidateState` (`ValidateState`);


ALTER TABLE `resultStatus`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
