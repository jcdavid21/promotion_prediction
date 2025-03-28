# Project Setup Guide

## Prerequisites
Ensure you have the following installed on your system:
- [Python](https://www.python.org/downloads/)
- [VS Code](https://code.visualstudio.com/)
- [PHP](https://www.php.net/downloads)
- MySQL Database

## Setup Instructions

### 1. Open the Project in VS Code
- Navigate to your project folder.
- Open the folder in VS Code.

### 2. Set Up Virtual Environment
- Open the terminal in VS Code.
- Run the following command to create a virtual environment:
  ```sh
  python -m venv venv
  ```
- Activate the virtual environment:
  - On Windows:
    ```sh
    venv\Scripts\activate
    ```
  - On macOS/Linux:
    ```sh
    source venv/bin/activate
    ```

### 3. Navigate to the Components Directory
- Change the directory to `components`:
  ```sh
  cd components
  ```

### 4. Run the Application
- Execute the following command to start the app:
  ```sh
  python app.py
  ```

### 5. Set Up the Database
- Create a database named `emp_performance_db` in MySQL.
- Import the SQL file located in `backend/dbase`.

### 6. Run PHP
- Start your PHP server.
- Use the following credentials to log in:
  - **Username:** admin
  - **Password:** admin

