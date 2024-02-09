CREATE TABLE admin (
a_id int PRIMARY KEY, 
a_name char(30) NOT NULL UNIQUE
);

CREATE TABLE region (
r_name char(30) PRIMARY KEY
);

CREATE TABLE server (
r_name char(30) REFERENCES region(r_name) ON DELETE CASCADE, 
s_num int, 
a_id int NOT NULL REFERENCES admin(a_id), 
PRIMARY KEY (r_name, s_num));

CREATE TABLE location (
l_name char(30) PRIMARY KEY
);

CREATE TABLE town (
l_name char(30) PRIMARY KEY,
architecture_style char(30),
FOREIGN KEY (l_name) REFERENCES location (l_name) ON DELETE CASCADE
);

CREATE TABLE dungeon (
l_name char(30) PRIMARY KEY,
enemy_difficulty char(30),
FOREIGN KEY (l_name) REFERENCES location (l_name) ON DELETE CASCADE
);

CREATE TABLE connected_to (
l_name1 char(30),
l_name2 char(30),
PRIMARY KEY (l_name1, l_name2),
FOREIGN KEY (l_name1) REFERENCES location (l_name),
FOREIGN KEY (l_name2) REFERENCES location (l_name)
);

CREATE TABLE rv (
rarity char(30) PRIMARY KEY,
value int
);

CREATE TABLE item (
i_name char(30) PRIMARY KEY,
rarity char(30) REFERENCES rv(rarity) 
);

CREATE TABLE npc (
n_id int PRIMARY KEY,
n_name char(30) NOT NULL
);

CREATE TABLE villager(
n_id int PRIMARY KEY REFERENCES npc(n_id) ON DELETE CASCADE, 
job char(30),
l_name char(30) REFERENCES town(l_name) 
);

CREATE TABLE mob (
n_id int PRIMARY KEY REFERENCES npc(n_id) ON DELETE CASCADE,
health int NOT NULL,
damage int NOT NULL,
kills int,
deaths int
);

CREATE TABLE sells (
n_id int REFERENCES villager(n_id),
i_name char(30) REFERENCES item(i_name),
PRIMARY KEY (n_id,i_name)
);

CREATE TABLE drops (
n_id int REFERENCES mob(n_id),
i_name char(30) REFERENCES item(i_name),
PRIMARY KEY (n_id,i_name)
);

CREATE TABLE spawns(
l_name char(30) REFERENCES dungeon(l_name),
n_id int REFERENCES mob(n_id),
spawn_rate int,
PRIMARY KEY (l_name, n_id)
);

CREATE TABLE lvl_stats(
lvl int PRIMARY KEY,
strength int,
health int
);

CREATE TABLE player (
p_id int PRIMARY KEY,
p_name char(30) NOT NULL UNIQUE,
join_date date,
money int,
lvl int NOT NULL REFERENCES lvl_stats(lvl),
l_name char(30) NOT NULL REFERENCES location(l_name)
);

CREATE TABLE friends_with(
p_id1 int,
p_id2 int,
PRIMARY KEY (p_id1, p_id2),
FOREIGN KEY (p_id1) REFERENCES player(p_id) ON DELETE CASCADE,
FOREIGN KEY (p_id2) REFERENCES player(p_id) ON DELETE CASCADE
);

CREATE TABLE guild (
g_name char(30) PRIMARY KEY,
logo char(500)
);

CREATE TABLE member_of(
p_id int,
g_name char(30),
role char(30),
PRIMARY KEY (p_id, g_name),
FOREIGN KEY (p_id) REFERENCES player(p_id) ON DELETE CASCADE,
FOREIGN KEY (g_name) REFERENCES guild(g_name)
);


CREATE TABLE owns(
p_id int REFERENCES player(p_id) ON DELETE CASCADE,
i_name char(30) REFERENCES item(i_name),
PRIMARY KEY (p_id, i_name)
);


CREATE TABLE is_on(
p_id int REFERENCES player(p_id) ON DELETE CASCADE,
r_name char(30),
s_num int,
con_status char(30),
PRIMARY KEY (p_id, r_name, s_num),
FOREIGN KEY (r_name, s_num) REFERENCES server(r_name, s_num)
);

CREATE TABLE has(
r_name char(30),
s_num int,
l_name char(30) REFERENCES location(l_name),
PRIMARY KEY (r_name, s_num, l_name),
FOREIGN KEY (r_name,s_num) REFERENCES server(r_name, s_num)
);


INSERT INTO admin VALUES (5214, 'bob');
INSERT INTO admin VALUES (4042332452, 'thomas');
INSERT INTO admin VALUES (5125, 'tom');
INSERT INTO admin VALUES (5892341, 'suzie');
INSERT INTO admin VALUES (123321, 'billy');

INSERT INTO region VALUES ('NA');
INSERT INTO region VALUES ('EU');
INSERT INTO region VALUES ('CHN');
INSERT INTO region VALUES ('SEA');
INSERT INTO region VALUES ('SA');

INSERT INTO server VALUES ('NA',3,5214);
INSERT INTO server VALUES ('NA',1,5125);
INSERT INTO server VALUES ('EU',2,4042332452);
INSERT INTO server VALUES ('CHN',5,123321);
INSERT INTO server VALUES ('NA',5,5892341);

INSERT INTO location VALUES ('Christmas Land');
INSERT INTO location VALUES ('Cool Town');
INSERT INTO location VALUES ('Spooky Dungeon');
INSERT INTO location VALUES ('Gingerbread Village');
INSERT INTO location VALUES ('Scary Land');
INSERT INTO location VALUES ('Firefly Treehouse');
INSERT INTO location VALUES ('Red Garden');
INSERT INTO location VALUES ('Big bad house');
INSERT INTO location VALUES ('Small bad house');
INSERT INTO location VALUES ('Dungeon #5');

INSERT INTO town VALUES ('Christmas Land', 'Candy');
INSERT INTO town VALUES ('Cool Town', 'Brutalism');
INSERT INTO town VALUES ('Gingerbread Village', 'Candy');
INSERT INTO town VALUES ('Firefly Treehouse', 'Nature');
INSERT INTO town VALUES ('Red Garden', 'Nature');


INSERT INTO dungeon VALUES ('Spooky Dungeon', 'Hard');
INSERT INTO dungeon VALUES ('Scary Land', 'Easy');
INSERT INTO dungeon VALUES ('Big bad house', 'Hard');
INSERT INTO dungeon VALUES ('Small bad house', 'Easy');
INSERT INTO dungeon VALUES ('Dungeon #5', 'Normal');

INSERT INTO connected_to VALUES ('Cool Town','Christmas Land');
INSERT INTO connected_to VALUES ('Christmas Land','Gingerbread Village');
INSERT INTO connected_to VALUES ('Gingerbread Village','Dungeon #5');
INSERT INTO connected_to VALUES ('Red Garden','Scary Land');
INSERT INTO connected_to VALUES ('Small bad house','Big bad house');

INSERT INTO rv VALUES ('NULL',0);
INSERT INTO rv VALUES ('Common', 1);
INSERT INTO rv VALUES ('Rare',20);
INSERT INTO rv VALUES ('Epic',100);
INSERT INTO rv VALUES ('Legendary', 10000);

INSERT INTO item VALUES ('Broadsword','Common');
INSERT INTO item VALUES ('Big bad Club','Legendary');
INSERT INTO item VALUES ('Sunglasses','Epic');
INSERT INTO item VALUES ('Stick','Common');
INSERT INTO item VALUES ('Wood Slab','NULL');

INSERT INTO npc VALUES (123512,'Ogre');
INSERT INTO npc VALUES (1257854,'Arnolda');
INSERT INTO npc VALUES (95434,'Billington');
INSERT INTO npc VALUES (5234,'Anchovy Jones');
INSERT INTO npc VALUES (65858,'Green Slime');
INSERT INTO npc VALUES (23454,'Sneaky Joe');
INSERT INTO npc VALUES (999,'Big Bad');
INSERT INTO npc VALUES (45435,'Small Bad');
INSERT INTO npc VALUES (5794,'Arlington The Great');
INSERT INTO npc VALUES (34535,'Billiard Bobby');
INSERT INTO npc VALUES (45745,'Friendly Caterpillar');

INSERT INTO villager VALUES (95434,'Smith','Cool Town');
INSERT INTO villager VALUES (1257854,'Milk Maid','Gingerbread Village');
INSERT INTO villager VALUES (23454,'Thief','Cool Town');
INSERT INTO villager VALUES (34535,'Mayor','Firefly Treehouse');
INSERT INTO villager VALUES (5794,'General','Christmas Land');
INSERT INTO villager VALUES (45745,'Mayor','Red Garden');

INSERT INTO mob VALUES (65858,10,1,0,1000);
INSERT INTO mob VALUES (123512,1000,20,8,891);
INSERT INTO mob VALUES (999,5000,120,1837,8);
INSERT INTO mob VALUES (45435,250,55,346,432);
INSERT INTO mob VALUES (5234,50,999999,7948283,1);

INSERT INTO sells VALUES (95434,'Broadsword');
INSERT INTO sells VALUES (1257854,'Wood Slab');
INSERT INTO sells VALUES (23454,'Stick');
INSERT INTO sells VALUES (34535,'Sunglasses');
INSERT INTO sells VALUES (5794,'Big bad Club');
INSERT INTO sells VALUES (45745,'Stick');
INSERT INTO sells VALUES (1257854,'Stick');
INSERT INTO sells VALUES (34535,'Stick');
INSERT INTO sells VALUES (5794,'Stick');
INSERT INTO sells VALUES (95434,'Sunglasses');
INSERT INTO sells VALUES (45745,'Sunglasses');
INSERT INTO sells VALUES (5794,'Sunglasses');
INSERT INTO sells VALUES (1257854,'Sunglasses');

INSERT INTO drops VALUES (65858,'Stick');
INSERT INTO drops VALUES (123512,'Big bad Club');
INSERT INTO drops VALUES (999,'Big bad Club');
INSERT INTO drops VALUES (45435,'Sunglasses');
INSERT INTO drops VALUES (5234,'Wood Slab');

INSERT INTO spawns VALUES ('Scary Land',65858,5);
INSERT INTO spawns VALUES ('Spooky Dungeon',123512,25);
INSERT INTO spawns VALUES ('Big bad house',999,1000);
INSERT INTO spawns VALUES ('Small bad house',45435,500);
INSERT INTO spawns VALUES ('Dungeon #5',5234,9999);
INSERT INTO spawns VALUES ('Scary Land',123512,100);
INSERT INTO spawns VALUES ('Spooky Dungeon',999,800);
INSERT INTO spawns VALUES ('Big bad house',45435,300);
INSERT INTO spawns VALUES ('Small bad house',123512,20);
INSERT INTO spawns VALUES ('Dungeon #5',999,1);

INSERT INTO lvl_stats VALUES (1,2,3);
INSERT INTO lvl_stats VALUES (2,3,4);
INSERT INTO lvl_stats VALUES (3,4,5);
INSERT INTO lvl_stats VALUES (4,5,6);
INSERT INTO lvl_stats VALUES (5,6,7);

INSERT INTO player VALUES (27,'TheLegend27',TO_DATE('2001-03-27','YYYY-MM-DD'),2700000,5,'Dungeon #5');
INSERT INTO player VALUES (123,'Wong',TO_DATE('2022-12-12','YYYY-MM-DD'),0,1,'Cool Town');
INSERT INTO player VALUES (35634,'Cheggman',TO_DATE('2021-12-12','YYYY-MM-DD'),27,4,'Cool Town');
INSERT INTO player VALUES (465456,'Aph',TO_DATE('2010-09-12','YYYY-MM-DD'),3447,3,'Christmas Land');
INSERT INTO player VALUES (3463,'xXN00bPwn3rXx',TO_DATE('2007-03-04','YYYY-MM-DD'),3,2,'Small bad house');
INSERT INTO player VALUES (6346,'some_guy',TO_DATE('2021-05-05','YYYY-MM-DD'),50,1,'Dungeon #5');
INSERT INTO player VALUES (8547,'some_other_guy',TO_DATE('2017-11-21','YYYY-MM-DD'),80,1,'Red Garden');
INSERT INTO player VALUES (35745,'a_third_guy',TO_DATE('2012-07-01','YYYY-MM-DD'),658,4,'Spooky Dungeon');
INSERT INTO player VALUES (4754,'who?',TO_DATE('2019-03-30','YYYY-MM-DD'),3,2,'Scary Land');
INSERT INTO player VALUES (92352,'her',TO_DATE('2003-05-05','YYYY-MM-DD'),5000,5,'Big bad house');


INSERT INTO friends_with VALUES (3463,465456);
INSERT INTO friends_with VALUES (123,35634);
INSERT INTO friends_with VALUES (123,465456);
INSERT INTO friends_with VALUES (35634,3463);
INSERT INTO friends_with VALUES (465456,35634);

INSERT INTO guild VALUES ('Nerds','
   _
 (.v.)
//-=-\\
(\_=_/)
 ^^ ^^
');
INSERT INTO guild VALUES ('Smiley Friends','(｡◕‿◕｡)');
INSERT INTO guild VALUES ('34567890','o.O');
INSERT INTO guild VALUES ('UBC eSports',';)');
INSERT INTO guild VALUES ('Sad Lads',':((');

INSERT INTO member_of VALUES  (27,'Sad Lads','Leader');
INSERT INTO member_of VALUES  (3463,'Nerds','Co-Leader');
INSERT INTO member_of VALUES  (123,'34567890','Member');
INSERT INTO member_of VALUES  (35634,'UBC eSports','Janitor');
INSERT INTO member_of VALUES  (465456,'Smiley Friends','Friend');

INSERT INTO owns VALUES (27,'Wood Slab');
INSERT INTO owns VALUES (123,'Sunglasses');
INSERT INTO owns VALUES (35634,'Stick');
INSERT INTO owns VALUES (465456,'Broadsword');
INSERT INTO owns VALUES (465456,'Stick');

INSERT INTO is_on VALUES (123,'NA',3,'Online');
INSERT INTO is_on VALUES (27,'EU',2,'Online');
INSERT INTO is_on VALUES (35634,'EU',2,'Offline');
INSERT INTO is_on VALUES (465456,'NA',5,'Online');
INSERT INTO is_on VALUES (3463,'NA',5,'Online');
INSERT INTO is_on VALUES (4754,'NA',1,'Online');
INSERT INTO is_on VALUES (6346,'EU',2,'Online');
INSERT INTO is_on VALUES (8547,'EU',2,'Offline');
INSERT INTO is_on VALUES (35745,'NA',5,'Online');
INSERT INTO is_on VALUES (92352,'NA',5,'Online');

INSERT INTO has VALUES ('NA',1,'Dungeon #5');
INSERT INTO has VALUES ('EU',2,'Cool Town');
INSERT INTO has VALUES ('EU',2,'Red Garden');
INSERT INTO has VALUES ('NA',5,'Christmas Land');
INSERT INTO has VALUES ('NA',5,'Small bad house');
