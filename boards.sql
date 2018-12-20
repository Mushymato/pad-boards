CREATE SCHEMA optimal;
CREATE TABLE boards (
    bID int PRIMARY KEY AUTO_INCREMENT,
    size nvarchar(1) NOT NULL DEFAULT 'm', -- s,m,l for 5x4,6x5,7x6
    pattern nvarchar(42) NOT NULL UNIQUE,
	orb_count int NOT NULL,
	description nvarchar(50)
);
CREATE TABLE orbs(
	bID int,
    FOREIGN KEY (bID) REFERENCES boards(bID) ON DELETE CASCADE,
	color nvarchar(1) NOT NULL,
	count int NOT NULL,
    UNIQUE(bID, color)
);
CREATE TABLE steps(
    sID int PRIMARY KEY AUTO_INCREMENT,
	bID int NOT NULL,
    FOREIGN KEY (bID) REFERENCES boards(bID) ON DELETE CASCADE,
	pattern_board nvarchar(42) NOT NULL,
	pattern_match nvarchar(42) NOT NULL
);
CREATE TABLE combos(
	cID int PRIMARY KEY AUTO_INCREMENT,
	bID int NOT NULL,
    FOREIGN KEY (bID) REFERENCES boards(bID) ON DELETE CASCADE,
	sID int NOT NULL,
    FOREIGN KEY (sID) REFERENCES steps(sID) ON DELETE CASCADE,
	color nvarchar(1) NOT NULL,
	length int NOT NULL,
	pattern_combo nvarchar(42) NOT NULL
);
CREATE TABLE styles(
	cID int NOT NULL,
    FOREIGN KEY (cID) REFERENCES combos(cID) ON DELETE CASCADE,
	style nvarchar(10) NOT NULL,
    UNIQUE(cID, style)
);