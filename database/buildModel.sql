Create Schema IF not EXISTS Marketplace;
use Marketplace;

create table IF not EXISTS Marketplace.Customer_Support (
    CS_ID int primary key,
    CS_Name varchar(30) not null,
    CS_Employee_Date date not null, 
    CS_Phone_Number varchar(15) not null unique,
    CS_location varchar(45) not null
);

-- Users table for application accounts (buyers/sellers/admin)
CREATE TABLE IF not EXISTS Marketplace.Users (
    User_ID int PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) not null UNIQUE,
    User_Email varchar(45) not null unique,
    PasswordHash VARCHAR(255) not null,
    Role VARCHAR(20) not null DEFAULT 'buyer',
    Created_At DATETIME not null DEFAULT CURRENT_TIMESTAMP,
    Valid_ID varchar(20),
    Full_Name VARCHAR(50),
    User_dob date
);

create table IF not EXISTS Marketplace.Buyer (
    Buyer_ID int,
    CS_ID int,
    Buyer_Phone_Number varchar(15) not null unique,
    Buyer_Location varchar(45) not null,
    FOREIGN KEY (CS_ID) REFERENCES Marketplace.Customer_Support(CS_ID),
    FOREIGN KEY (Buyer_ID) REFERENCES Marketplace.Users(User_ID) ON DELETE CASCADE
);

create table IF not EXISTS Marketplace.Cart (
    Cart_ID int primary key,
    Buyer_ID int not null unique,
    Cart_Cost_Amount float not null,
    FOREIGN KEY (Buyer_ID) REFERENCES Marketplace.Buyer(Buyer_ID) ON DELETE CASCADE
);

CREATE TABLE IF not EXISTS Marketplace.Seller (
    Seller_ID int,
    CS_ID int,
    Seller_Phone_Number VARCHAR(15) not null UNIQUE,
    Seller_Stars int not null CHECK (Seller_Stars >= 1 AND Seller_Stars <= 5),
    Seller_Location VARCHAR(45) not null,
    FOREIGN KEY (CS_ID) REFERENCES Marketplace.Customer_Support(CS_ID),
    FOREIGN KEY (Seller_ID) REFERENCES Marketplace.Users(User_ID) ON DELETE CASCADE
);

-- IF seller is deleted, all his items are deleted too, so backend needs to deal with the case!!!!!!!!!
create table IF not EXISTS Marketplace.Item (
    Item_ID int primary key,
    Seller_ID int not null,
    Cart_ID int,
    Item_Name varchar(30) not null,
    Item_Description varchar(100) not null,
    Item_Price float not null,
    Item_Tags varchar(45),
    Item_Quantity int not null,
    Added_On datetime not null,
    -- Item_Image varchar(100),   may be as a BLOB or link to a Object Storage Service
    FOREIGN KEY (Seller_ID) REFERENCES Marketplace.Seller(Seller_ID) ON DELETE CASCADE,
    FOREIGN KEY (Cart_ID) REFERENCES Marketplace.Cart(Cart_ID)
);

CREATE TABLE IF not EXISTS Marketplace.User_History (
    History_ID int PRIMARY KEY AUTO_INCREMENT,
    User_ID int not null,
    Item_ID int not null,
    History_Type ENUM('view', 'purchase') not null DEFAULT 'view',
    Quantity int DEFAULT null,  -- used for purchases, null for views
    Viewed_At datetime not null,
    Purchased_At datetime not null,
    FOREIGN KEY (User_ID) REFERENCES Marketplace.Users(User_ID) ON DELETE CASCADE,
    FOREIGN KEY (Item_ID) REFERENCES Marketplace.Item(Item_ID) ON DELETE CASCADE
);

create table IF not EXISTS Marketplace.Payment(
    Payment_ID int primary key,
    Cart_ID int not null,
    Payment_Date date not null,
    Payment_Type int not null,
    Payment_Total float not null,
    FOREIGN KEY (Cart_ID) REFERENCES Marketplace.Cart(Cart_ID) ON DELETE CASCADE
);

create table IF not EXISTS Marketplace.Payment_Method(
    Payment_Method_ID int primary key,
    Buyer_ID int not null,
    Payment_Method_Type varchar(45) not null,
    Payment_Method_Number varchar(20) not null unique,
    Payment_Method_expiration int not null,
    Payment_Method_CVV int not null,
    FOREIGN KEY (Buyer_ID) REFERENCES Marketplace.Buyer(Buyer_ID) ON DELETE CASCADE
);

create table IF not EXISTS Marketplace.Review(
    Review_ID int primary key,
    Review_text varchar(255) not null,
    Review_Rating int not null check (Review_Rating >= 1 and Review_Rating <= 5),
    Review_Date date not null,
    Buyer_ID int not null,
    Item_ID int not null,
    FOREIGN KEY (Buyer_ID) REFERENCES Marketplace.Buyer(Buyer_ID) ON DELETE CASCADE,
    FOREIGN KEY (Item_ID) REFERENCES Marketplace.Item(Item_ID) ON DELETE CASCADE
);


create table IF not EXISTS Marketplace.Orders(
    Order_ID int primary key auto_increment,
    Instance_ID int,
    Seller_ID int, 
    Buyer_ID int,
    Item_Name varchar(30) not null,
    Item_Description varchar(100) not null,
    Item_Price float not null,
    Item_Tags varchar(45),
    Order_Placed datetime not null
);



-- Will hold individual instances of items
create table IF not EXISTS Marketplace.Item_Instance(
    Instance_ID int PRIMARY KEY AUTO_INCREMENT,
    Item_ID int not null,
    Seller_ID int not null,
    Item_Name varchar(30) not null,
    Item_Description varchar(100) not null,
    Item_Price float not null,
    Item_Tags varchar(45),
    Added_On datetime not null,
    
    FOREIGN KEY (Item_ID) REFERENCES Marketplace.Item(Item_ID) ON DELETE CASCADE,
    FOREIGN KEY (Seller_ID) REFERENCES Marketplace.Seller(Seller_ID) ON DELETE CASCADE
);


--  How we delete items when user buys an item
--  DELETE FROM Marketplace.Item_Instance
--  WHERE Item_ID = 42  $item[id]
--  LIMIT 1 $item['quantity]


--  Will automatically create instances of an item depending on quantity
--  when inserting an item into Item table


CREATE TRIGGER trg_item_after_insert
AFTER INSERT ON Marketplace.Item
FOR EACH ROW
BEGIN
    DECLARE counter int DEFAULT 0;

    WHILE counter < NEW.Item_Quantity DO
        INSERT intO Marketplace.Item_Instance (
            Item_ID, 
            Seller_ID, 
            Item_Name, 
            Item_Description, 
            Item_Price, 
            Item_Tags, 
            Added_On
        )
        VALUES (
            NEW.Item_ID,
            NEW.Seller_ID,
            NEW.Item_Name,
            NEW.Item_Description,
            NEW.Item_Price,
            NEW.Item_Tags,
            NEW.Added_On
        );

        SET counter = counter + 1;
    END WHILE;
END;


-- Will automaitcally add instances if user adds more quantity

CREATE TRIGGER trg_item_after_update
AFTER UPDATE ON Marketplace.Item
FOR EACH ROW
BEGIN
    DECLARE existing_count int;
    DECLARE to_add int;
    DECLARE i int DEFAULT 0;

    -- Count existing instances
    SELECT COUNT(*)
    intO existing_count
    FROM Marketplace.Item_Instance
    WHERE Item_ID = NEW.Item_ID;

    -- How many additional instances needed
    SET to_add = NEW.Item_Quantity - existing_count;

    IF to_add > 0 THEN
        WHILE i < to_add DO
            INSERT intO Marketplace.Item_Instance (
                Item_ID, 
                Seller_ID, 
                Item_Name, 
                Item_Description, 
                Item_Price, 
                Item_Tags, 
                Added_On
            )
            VALUES (
                NEW.Item_ID,
                NEW.Seller_ID,
                NEW.Item_Name,
                NEW.Item_Description,
                NEW.Item_Price,
                NEW.Item_Tags,
                NEW.Added_On
            );

            SET i = i + 1;
        END WHILE;
    END IF;
END;

