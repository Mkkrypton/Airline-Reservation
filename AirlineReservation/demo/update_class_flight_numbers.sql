-- SQL script to update class table flight numbers to match flight table

DELETE FROM class;

INSERT INTO class (number, name, capacity, price) VALUES
('IN001','Business',5,500),
('IN001','Economy',200,180),
('IN002','Business',15,4000),
('IN002','Economy',100,1000),
('IN003','Business',1,200),
('IN003','Economy',100,100),
('IN004','Business',15,800),
('IN004','Economy',100,240),
('IN005','Business',10,200),
('IN005','Economy',100,100),
('IN006','Business',5,200),
('IN006','Economy',80,50),
('IN007','Business',3,300),
('IN007','Economy',50,60),
('IN008','Business',3,100),
('IN008','Economy',100,40),
('IN009','Business',10,100),
('IN009','Economy',200,40),
('IN010','Business',3,200),
('IN010','Economy',80,50);
