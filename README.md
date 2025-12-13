# Marketplace_449

An online marketplace where individuals can browse, search, and purchase a variety of products, including branded food, clothing, and utilities.

---

## How to Run Project

1. Clone the repository
  ```bash
  https://github.com/Josh-6/Marketplace_449.git
  ```
2. Install XAMPP
   
  - go to
```bash
  https://www.apachefriends.org/
  ```
  Only need to install these components:
  - Apache
  - MySQL
  - PHP
  - phpMyAdmin

  Go to XAMPP file location 
  By default it is located in:
  `C:\xampp`
  Put the application files in the htdocs folder


3. Open install the libraries inside requirements.txt
- Optional but recomended: use a virtual environment to install the libraries and its dependencies to avoid error or clogging your pc.
  - You can use: ``` python -m venv devEnv ```
  - Next activate the virtual environment ``` devEnv\Scripts\activate ```
  - Then install the dependencies
- use pip install or any other method to install them.

4. Find htdocs in XAMPP folder
- Windows → C:\xampp\htdocs\
- Mac → /Applications/XAMPP/htdocs/
- Linux → /opt/lampp/htdocs/
  
5. Move or copy Marketplace_449 to htdocs
- Windows → C:\xampp\htdocs\Marketplace_449
- Mac → /Applications/XAMPP/htdocs/Marketplace_449
- Linux → /opt/lampp/htdocs/Marketplace_449

6. Using XAMPP App
- start the "Apache" module
- start the "MySQL" Module

7. Using any mySQL DBMS
- create a connection
- copy the XAMPP port and add it on the set up option "Port"
- set a password if needed
- Optional - set a devide as a server so that it can be accessible by different collaborators. Needs to have further configuration...

8. Go to your browser and visit
- (only the first time)
  ```bash
  http://localhost/marketplace_449/database/
  ```
  and choose either
  - appDBSetup option to set up the Models and populate with dummy data
  - DBbuildModel for just the model
  - sampleData to populate the schema with data

- Or if already set up go directly to:
  ```bash
  http://localhost/marketplace_449/frontend/
  ```



