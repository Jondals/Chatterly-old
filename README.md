# **Chatterly-Old**

Chatterly is a real-time messaging and voice-calling web application inspired by Discord. It was developed using PHP, MySQL, JavaScript, and WebRTC.

The project was created to explore technologies related to peer-to-peer communication, voice calling, file sharing, real-time messaging, database CRUD operations, and user authentication.

## Features

### Authentication System

- User registration
- Secure login and logout
- Session-based authentication
- Protected routes and user validation

### Messaging

- Private one-to-one conversations
- Real-time message updates
- Message history persistence
- User selection and conversation management

### File Sharing

- Upload and send files
- Server-side file storage

### Voice Calling

- Browser-based voice calls using WebRTC
- Call signaling implemented through PHP and the database
- Accept and reject incoming calls
- Microphone and camera controls during calls

### User Management

- Online user listing
- Active conversation tracking
- Session management

# Requirements

- XAMPP
- PHP 8.0 or newer
  
# Installation

## 1.- Clone the Repository
```
git clone https://github.com/yourusername/chatterly.git
```

## 2.- Move the Project to XAMPP
Copy the project folder into:
```
xampp/htdocs/
```
Example:
```
xampp/htdocs/chatterly
```

## 3.- Start Apache and MySQL
Open XAMPP Control Panel and start:
- Apache
- MySQL

## 4. Import the Database Schema
Import the SQL schema provided with the project:
```
database/chatterly.sql
```

## 6. Run the Application
```
http://localhost/chatterly
```

## Ejecutar sin XAMPP (versión actual)
La app usa **SQLite** (PDO) en lugar de MySQL: ya no hace falta XAMPP. Ejecuta `iniciar.bat` (usa `php -S localhost:8000`) y la base de datos se crea sola en `data/`.
Usuarios demo: `demo1` / `demo2`, contraseña `Demo!1`. También hay un botón **Entrar como usuario** (invitado). `db.sql` es el esquema MySQL original, solo de referencia.

## Demo en Vercel
Web: https://chatterly-old.vercel.app (runtime comunitario `vercel-php`). Es una demo con limitaciones:
- La BD SQLite vive en `/tmp`: **los datos se pierden** cuando la función se reinicia y pueden no compartirse entre instancias.
- Las sesiones también son locales a cada instancia.
- La subida de fotos de perfil y de archivos del chat **no funciona** (disco de solo lectura).
- Las llamadas WebRTC (señalización por polling) pueden ser poco fiables.
