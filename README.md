# pink-flowers
The Florals Web Application is an e-commerce platform designed for a flower shop, allowing users to browse, select, and purchase various floral products online. Built using PHP, MySQL, HTML, CSS, and JavaScript, this responsive and dynamic website provides a user-friendly interface for both customers and administrators.

The core functionality of the website includes user registration and login, product browsing, wishlist and cart management, and secure order placement. Users can add items to their cart or wishlist using AJAX-based buttons, enabling a seamless experience without page reloads. The shopping cart supports quantity updates and item removal, and displays real-time totals. The checkout page includes a secure form where users enter delivery and payment information before finalizing their order.

To ensure data persistence and organization, all information is stored in a MySQL database. Key tables include users, products, orders, and order_items. When an order is placed, both order metadata and detailed item data are saved in the database. Users must be logged in to place an order, which enforces session-based security.

An admin dashboard is provided to manage the store’s inventory. Administrators can add, update, and delete products, including uploading images which are stored securely in a dedicated uploads/ directory. This makes the system easily extensible and maintainable.

Security is a critical aspect of the system. The application implements CSRF protection, password hashing, and login rate-limiting to prevent brute-force attacks. Optional features like email-based OTP verification and SMS-based authentication can be integrated for two-factor login security using tools like PHPMailer and Twilio.

Overall, the Florals project demonstrates practical knowledge in full-stack web development, user authentication, secure form handling, admin control systems, and dynamic e-commerce functionality. It is scalable, secure, and designed with real-world usability in mind.
