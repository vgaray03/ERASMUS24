# ERASMUS24

# Game Project

This repository contains the development of a multiplayer web-based game platform. The project implements the game "Cant-Stop".

### Game Features
- Human vs. Human gameplay with a graphical interface.
- Game state stored in a MySQL database. (Currently does not work)
- Connection initialization and authentication (even without password). (Currently does not work)
- Turn validation, deadlock detection, and game rule enforcement.

### Technologies Used
- **Backend**: PHP, MySQL, AJAX, jQuery
- **Frontend**: HTML, CSS, JavaScript (with possible frameworks like React or Vue.js)
- **Version Control**: Git, GitHub

---

## Project Structure

.projectERASMUS24/ 
├── backend api/ 
│   ├── api.php # API endpoints for game logic 
│   ├── db.php # Database connection 
│   ├── game_rules.php # Game rules validation 
│   └── player_management.php # Player authentication and state 
├── database/ 
│   ├── Database Schema.sql # MySQL schema for storing game state and player data 
├── frontend/ 
│   ├── index.html # Main game interface 
│   ├── style.css # Game UI styling 
│   ├── script.js # Game interaction scripts (AJAX requests, etc.) 
│   └── images/ # Any images (icons, assets for the frontend) 
├── README.md # Project description and setup instructions 
└── .gitignore # Git ignore file

---

## How to Run the Project Locally

1. **Clone the repository:**

   ```bash
   git clone https://github.com/iee-ihu-gr-course1941/ERASMUS24.git
   cd ERASMUS24

2. **Run the index.html file on your browser:**

Double click on the index.html file to run the game and play locally.

The authent.html file is used to record the player username, though this feature is incomplete and does not work.

## Acknowledgments

    GitHub for version control and project management

---

### Explanation of Sections:

- **Project Overview**: Provides a brief description of the project and the game features.
- **Technologies Used**: Lists the core technologies involved in the project.
- **Project Structure**: Describes the folder structure of the repository for better organization.
- **How to Run the Project Locally**: Provides step-by-step instructions for setting up and running the project on a local machine.
- **Acknowledgments**: A place to acknowledge any tools, services, or resources that helped in the development.

---

