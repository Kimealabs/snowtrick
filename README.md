<a name="readme-top"></a>

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/5e09fd57a59e496489d9d3ab735279be)](https://www.codacy.com/gh/Kimealabs/snowtricks/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Kimealabs/snowtricks&amp;utm_campaign=Badge_Grade)
<img src="https://img.shields.io/badge/HTML-black?style=flat-square&logo=HTML5" />
<img src="https://img.shields.io/badge/Javascript-black?style=flat-square&logo=Javascript" />
<img src="https://img.shields.io/badge/CSS-black?style=flat-square&logo=CSS3" />
<img src="https://img.shields.io/badge/PHP 8.1-black?style=flat-square&logo=Php" />
<img src="https://img.shields.io/badge/LICENCE-MIT-blue" />

<br />
<div align="center">
  <a href="https://github.com/Kimealabs/snowtricks">
    <img src="public/assets/img/snowboard.jpg" alt="Logo" width="160" height="120">
  </a>

  <h3 align="center">SNOWTRICKS</h3>

  <p align="center">
    An Open Class Rooms Project
    <br />
    <a href="https://github.com/Kimealabs/snowtricks/"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://github.com/Kimealabs/snowtricks/issues">Report Bug</a>
    ·
    <a href="https://github.com/Kimealabs/snowtricks/issues">Request Feature</a>
  </p>
</div>



<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
      <ul>
        <li><a href="#my-development-environment">My development environment</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#acknowledgments">Acknowledgments</a></li>
  </ol>
</details>



<!-- ABOUT THE PROJECT -->
## About The Project

Here is the Snowtricks project of my OpenClassRooms (P6) formation "PHP Symfony Dev".

Purpose: Create with the Symfony framework a blog system on snowboard tricks, this website must:

- Register new user with email confirmation.
- Connect users.
- Propose a secure system to change the forgotten password.
- Publish discussion posts on the tricks pages.
- Create trick pages with media (images, Youtube sharing).
- Edit/delete trick pages.
- List all tricks on the homepage.


<p align="right">(<a href="#readme-top">back to top</a>)</p>


<!-- DEV ENV -->
## My development environment 
### Here the list of frameworks, programs and libraries

<img src="https://img.shields.io/badge/Symfony 6.1.4-black?style=for-the-badge&logo=Symfony" />  <img src="https://img.shields.io/badge/Symfony CLI 5.4.11-black?style=for-the-badge&logo=Symfony" />

<img src="https://img.shields.io/badge/Composer 2.3.10-280?style=for-the-badge&logo=Composer" /> <img src="https://img.shields.io/badge/Twig 3.4.2-green?style=for-the-badge" />

<img src="https://img.shields.io/badge/PHP 8.1-eef?style=for-the-badge&logo=PHP" /> <img src="https://img.shields.io/badge/Apache 2.4.54-fa0303?style=for-the-badge&logo=Apache" /> <img src="https://img.shields.io/badge/PhpMyAdmin 5.2.0-f2cb61?style=for-the-badge&logo=phpMyAdmin" />


<img src="https://img.shields.io/badge/VSCode 1.71.0-0055aa?style=for-the-badge&logo=Visual Studio Code" />

<img src="https://img.shields.io/badge/Docker 4.11.1-eee?style=for-the-badge&logo=Docker" />  <img src="https://img.shields.io/badge/WSL2 with Ubuntu 20.04 LTS-eee?style=for-the-badge&logo=Ubuntu" />



<img src="https://img.shields.io/badge/Boostrap 5.2.0-f1dff1?style=for-the-badge&logo=Bootstrap" /> <img src="https://img.shields.io/badge/JQuery 3.2.1-orange?style=for-the-badge&logo=Jquery" /> <img src="https://img.shields.io/badge/FontAwesome 6.1.2-1b3e9c?style=for-the-badge&logo=Font Awesome" />

<img src="https://img.shields.io/badge/MailHog-b9140c?style=for-the-badge&logo=Mail.Ru" />


<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- GETTING STARTED -->
## Getting Started

How to install and configure snowtricks

### Prerequisites

- PHP version 8.0.2 or higher
- AMP environment if local use (MAMP, WAMP) OR install Docker (docker-compose up with docker-compose.yml) and employ php bin/console serve:start
- You can install Symfony CLI to facilitate commands

### Installation

Below is an example of how you can install on local with Docker.

1. Clone the repo into your directory
2. Make a composer update / composer install
3. Change the database URL into .env with your credentials and parameters
4. Run docker-compose up  (or use Make file -> make start for 4-5 | make install for 4-5-6-7-8)
5. Run php bin/console server:start
6. php bin/console database:create
7. php bin/console doctrine:migrations:migrate
8. php bin/console doctrine:fixtures:load (add Categories, fictitious users and 10 examples of tricks into database)
9. Change .env MAIL_DSN with your smtp system (if you don't want develop or use personal Email system)

Now you can Open 127.0.0.1:8000 for Website into your Web Navigator
* :8082 for PhpMyAdmin
* :8025 for Mailhog (simulate Email Client)
      
      
11. ENJOY :-)

<p align="right">(<a href="#readme-top">back to top</a>)</p>


<!-- CONTRIBUTING -->
## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<p align="right">(<a href="#readme-top">back to top</a>)</p>


<!-- ACKNOWLEDGMENTS -->
## Acknowledgments

This is a list of resources you find helpful and i would like to give credit to !

* [Lior Chamla Symfony 5 Tuto](https://www.youtube.com/watch?v=4t3fNkGwRWo)
* [Benoit - nouvelle-techno.fr](https://nouvelle-techno.fr/)
* [Img Shields](https://shields.io)
* [Font Awesome](https://fontawesome.com)
* [UML tools](https://gitmind.com)
* [OCR cours Symfony 5](https://openclassrooms.com/fr/courses/5489656-construisez-un-site-web-a-l-aide-du-framework-symfony-5)


<p align="right">(<a href="#readme-top">back to top</a>)</p>
