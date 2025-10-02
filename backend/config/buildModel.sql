Create Schema IF NOT EXISTS Marketplace;

create table IF NOT EXISTS Marketplace."Buyer" (
    Buyer_ID int primary key,
    CS_ID int,
    Buyer_Email varchar(45) not null unique,
    Buyer_DateOfBirth date not null,
    Buyer_Phone_Number varchar(15) not null unique,
    Buyer_Name varchar(30) not null,
    Buyer_Valid_ID varchar(20) not null unique,  -- not sure what is this for --
    Buyer_Location varchar(45) not null
    FOREIGN KEY (CS_ID) REFERENCES Marketplace."Customer_Support"(CS_ID)
);

create table IF NOT EXISTS Marketplace."Cart" (
    Cart_ID int primary key,
    Buyer_ID int not null unique,
    Cart_Cost_Amount float not null,
    FOREIGN KEY (Buyer_ID) REFERENCES Marketplace."Buyer"(Buyer_ID) ON DELETE CASCADE
);

create table IF NOT EXISTS Marketplace."Customer_Support" (
    CS_ID int primary key,
    CS_Name varchar(30) not null,
    CS_Employee_Date date not null, 
    CS_Phone_Number varchar(15) not null unique,
    CS_location varchar(45) not null
);

create table IF NOT EXISTS Marketplace."Seller" (
    Seller_ID int primary key,
    CS_ID int,
    Seller_Name varchar(30) not null,
    Seller_Email varchar(45) not null unique,
    Seller_DateOfBirth date not null,
    Seller_Valid_ID int not null unique,
    Seller_Phone_Number varchar(15) not null unique,
    Seller_Stars int check (Seller_Stars >= 1 and Seller_Stars <= 5) not null,
    Seller_Location varchar(45) not null,
    FOREIGN KEY (CS_ID) REFERENCES Marketplace."Customer_Support"(CS_ID) 
);

-- IF seller is deleted, all his items are deleted too, so backend needs to deal with the case!!!!!!!!!
create table IF NOT EXISTS Marketplace."Item"(
    Item_ID int primary key,
    Seller_ID int not null,
    Cart_ID int,
    Item_Name varchar(30) not null,
    Item_Description varchar(100) not null,
    Item_Price float not null,
    Item_Tags varchar(45),
    Item_Quantity int not null,
    -- Item_Image varchar(100),   may be as a BLOB or link to a Object Storage Service
    FOREIGN KEY (Seller_ID) REFERENCES Marketplace."Seller"(Seller_ID) ON DELETE CASCADE
    FOREIGN KEY (Cart_ID) REFERENCES Marketplace."Cart"(Cart_ID)
);

create table IF NOT EXISTS Marketplace."Payment"(
    Payment_ID int primary key,
    Cart_ID int not null,
    Payment_Date date not null,
    Payment_Type int not null,
    Payment_Total float not null,
    FOREIGN KEY (Cart_ID) REFERENCES Marketplace."Cart"(Cart_ID) ON DELETE CASCADE
);

create table IF NOT EXISTS Marketplace."Payment_Method"(
    Payment_Method_ID int primary key,
    Buyer_ID int not null,
    Payment_Method_Type varchar(45) not null,
    Payment_Method_Number varchar(20) not null unique,
    Payment_Method_expiration int not null,
    Payment_Method_CVV int not null,
    FOREIGN KEY (Buyer_ID) REFERENCES Marketplace."Buyer"(Buyer_ID) ON DELETE CASCADE
);

create table IF NOT EXISTS Marketplace."Review"(
    Review_ID int primary key,
    Review_text varchar(255) not null,
    Review_Rating int check (Review_Rating >= 1 and Review_Rating <= 5) not null,
    Review_Date date not null,
    Buyer_ID int not null,
    Item_ID int not null,
    FOREIGN KEY (Buyer_ID) REFERENCES Marketplace."Buyer"(Buyer_ID) ON DELETE CASCADE,
    FOREIGN KEY (Item_ID) REFERENCES Marketplace."Item"(Item_ID) ON DELETE CASCADE
);
