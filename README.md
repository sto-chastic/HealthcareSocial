README.md

Healthcare Social Portal

Healthcare Social Portal is a PHP-based web application designed to facilitate seamless interaction between doctors and patients in a dedicated social network for healthcare. This platform enables doctors and patients to share information, schedule appointments, and help patients find the right specialists with ease.

Features

Patient Features

	•	Specialist Search: Patients can search for doctors based on specialization, location, and availability.
	•	Appointment Scheduling: Schedule, reschedule, or cancel appointments directly with doctors.
	•	Health Updates: Post and receive updates, share symptoms, and ask health-related questions within the community.
	•	Secure Messaging: Communicate privately with doctors for consultation or follow-ups.

Doctor Features

	•	Profile Management: Maintain a professional profile, including specialties, clinic details, and availability.
	•	Appointment Management: View, approve, and manage patient appointments.
	•	Patient Engagement: Share articles, answer questions, and engage with patients through posts.
	•	Secure Messaging: Provide consultations and follow-ups in a private and secure manner.

Shared Features

	•	Social Feed: Both patients and doctors can participate in a community feed to share medical knowledge and updates.
	•	Notifications: Stay informed about new appointments, messages, and community interactions.
	•	Role-Based Access: Patients and doctors have access to specific features based on their roles.

Installation

	1.	Clone the Repository

git clone https://github.com/yourusername/HealthcareSocialPortal.git
cd Healthcare SocialPortal


	2.	Set Up the Environment
Ensure you have a working PHP environment with a web server (e.g., Apache) and a database (e.g., MySQL).
	3.	Configure the Database
	•	Create a database for the project.
	•	Import the provided SQL file located in the database directory:

mysql -u [username] -p [database_name] < config/medi_connect.sql


	4.	Update Environment Settings
Rename the .env.example file to .env and configure your database credentials:

DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password


	5.	Install Dependencies
Run the following command to install required PHP packages:

composer install


	6.	Start the Server
Launch your local server or use PHP’s built-in server for testing:

php -S localhost:8000


	7.	Access the Portal
Open a web browser and visit:
http://localhost:8000

Usage

	•	Patients: Sign up, complete your profile, search for specialists, and schedule appointments.
	•	Doctors: Sign up, build your profile, manage appointments, and engage with patients.
	•	Admins (optional): Manage user accounts, monitor platform activity, and enforce platform policies.

Technologies Used

	•	Frontend: HTML, CSS, JavaScript
	•	Backend: PHP
	•	Database: MySQL
	•	Authentication: Secure login and role-based access
	•	Other Tools: Composer (for dependency management)

License

This project is licensed under the GNU License. See the LICENSE file for details.

Contact

For questions or feedback, please contact:
Email: d.jimenez298@uniandes.edu.co
