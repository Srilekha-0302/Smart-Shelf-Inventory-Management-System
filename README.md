# Smart-Shelf-Inventory-Management-System
🛒 Smart Shelf – A web-based Inventory Management System with role-based access, low stock &amp; expiry alerts, and sales tracking for departmental stores. Built using PHP, MySQL, HTML, CSS, and JavaScript.


---

## 🛠️ How to Run the Project

1. 🔽 **Download or Clone** this repository.
2. 🛠️ Open XAMPP, start **Apache** and **MySQL**.
3. 📁 Place the project folder in `htdocs` directory.
4. 🌐 Navigate to `http://localhost/smart-shelf/` in your browser.
5. 🗃️ Import the `inventory.sql` file into **phpMyAdmin** to set up the database.
6. 🔑 Login using:
   - **Manager:** `manager1` / `pass123`
   - **Cashier:** `cashier1` / `pass123`

---

## 📦 Database Structure

**Table:** `products`

| Field         | Type        | Description                    |
|---------------|-------------|--------------------------------|
| id            | INT         | Primary Key                    |
| name          | VARCHAR     | Product Name                   |
| quantity      | INT         | Stock Count                    |
| price         | FLOAT       | Price per unit                 |
| expiry_date   | DATE        | Expiration date of the product |
| category      | VARCHAR     | Product category               |



## 👨‍💻 Authors

- **Ravuri Sai Srilekha**
- **Somapuram Sahithi**

---
## 📸 Screenshots

### 🛒 Login Page
![Dashboard](Images/Screenshot%20(30).png)

### 🛒 Dashboard View
![Dashboard](Images/Screenshot%20(31).png)

### 📈 Most Popular Product Insights
![Expiry](Images/Screenshot%20(35).png)

### ⏰ Heat Maps
![Popular Product](Images/Screenshot%20(36).png)

### 🔐 Cashier Page
![Login](Images/Screenshot%20(37).png)

---

## 📄 Documentation

📄 Full project documentation: [Smart Shelf – Google Doc](https://docs.google.com/document/d/1E0RsS3dFs4Y6onUgT6YszVDBl5Nbln9g/edit?rtpof=true)  

---

## 💬 Future Scope

- 🔁 Barcode Scanner Integration  
- ☁️ Cloud-hosted database for multi-store support  
- 📱 Mobile app version  
- 🧠 AI for demand forecasting and smart alerts

---

## 💖 Inspiration

To solve real-world inventory challenges in retail environments with a smart, scalable solution that blends simplicity and efficiency.

> *"Keeping your shelves smart and your heart full, just like you do to mine, love." 🌷*
