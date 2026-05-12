# 🎬 AniList Web Tracker

A dynamic web application built with **Laravel (PHP)** that integrates with the **AniList GraphQL API**. This project demonstrates Backend Engineering concepts including MVC architecture, third-party API consumption, and robust error handling. And somewhat creativity on what stats are interesting that anilist doesn't have!

> **Status:** Active / Learning Project  
> **Live Demo:** Broken

## 🚀 Key Features

* **User Profile Search:** Fetch real-time user data including avatars, bio, and watch/read statistics. And some unshown stats from anilist and only could be taken through anilist's API.
* **AniList Tag Bypass:** Anilist has a limit of viewing only top 30 tags (top 100 for Tier 3 Donator), This uses anilist graphql (backend) and basically bypasses that and shows all.
![Tag](screenshots/tag.png)
* **Dynamic Data Fetching:** Uses raw GraphQL queries sent via Laravel's HTTP Client.
* **Robust Error Handling:** Gracefully handles API timeouts and empty states (e.g., users with no history).
* **Responsive UI:** Custom CSS Grid layout for manga cards and profile stats.
* **Dynamic Routing:** Route links are dynamic and scalable.

## 🛠️ Tech Stack

* **Backend:** PHP 8.x, Laravel Framework
* **API:** AniList GraphQL API
* **Frontend:** Blade Templates, CSS3 (Custom Grid/Flexbox)
* **Tools:** Composer, Git, VS Code

## 📸 Screenshots

| User Search Profile | Tag Bypass System |
|:-------------------:|:-----------------:|
| ![Profile Page1](screenshots/s1.png) | ![Tag Bypass1](screenshots/t1.png) |
| ![Profile Page2](screenshots/s2.png) | ![Tag Bypass2](screenshots/t2.png) |

## ⚙️ How to Run Locally

If you want to clone and run this project on your own machine:

1.  **Clone the repository**
    ```bash
    git clone [https://github.com/yion81/anilist-app.git](https://github.com/yion81/anilist-app.git)
    cd anilist-app
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    ```

3.  **Environment Setup**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Run the Server**
    ```bash
    php artisan serve
    ```

5.  **Visit the App**
    Open `http://localhost:8000` in your browser.

## 🧠 What I Learned (Technical Highlights)

This project was built to transition from Vanilla PHP to a structured Framework environment. Key learnings include:

* **MVC Architecture:** Separating logic (Controllers) from presentation (Blade Views) and routing.
* **Dynamic Routing:** Instead of creating a separate route for every single user, we created one pattern that matches millions of users.
* **API Integration:** Moving from verbose `cURL` to Laravel's fluent `Http` client.
* **Data Sanitization:** Using Blade's `{{ }}` syntax to prevent XSS attacks compared to `echo`.
* **GraphQL:** Writing and structuring complex queries to fetch nested JSON data (Media -> Title -> English/Romaji).
* **Outlier Calculations:** Instantly recognizes a data that is calculated might have outlier, eg (Deviation Range = (Meanscore-Deviation) till (Meanscore+Deviation) This could lead to 90+12 = 102% which is not possible, so immediately clamp it). 

## 📝 License

This project is open-source and available under the [MIT license](https://opensource.org/licenses/MIT).