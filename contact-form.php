<?php
/*
Plugin Name: Contact Form Plugin
Plugin URI: https://www.example.com/contact-form-plugin
Description: This plugin adds a contact form to the website.
Version: 1.0
Author: Hamza lachehab
Author URI: https://www.example.com/
License: GPL2
*/

// Register the shortcode
function contact_form_shortcode()
{
    ob_start();
    contact_form_html();
    return ob_get_clean();
}
add_shortcode('contact_form', 'contact_form_shortcode');

// Display the contact form HTML
function contact_form_html()
{
    
?>
<style>
    #success-message {
  display: none;
  background-color: #3e8e41;
  color: white;
  padding: 10px;
  margin-bottom: 10px;
}

</style>
<form id="contact-form" method="post">
  <label for="sujet">Sujet :</label>
  <input type="text" name="sujet" required>
  <br>
  <label for="nom">Nom :</label>
  <input type="text" name="nom" required>
  <br>
  <label for="prenom">Prénom :</label>
  <input type="text" name="prenom" required>
  <br>
  <label for="email">Email :</label>
  <input type="email" name="email" required>
  <br>
  <label for="message">Message :</label>
  <textarea name="message" required></textarea>
  <br>
  <div id="success-message">test</div> 
  <br>
  <input type="submit" name="submit" value="Envoyer">
</form>

<?php
}

    if (isset($_POST['submit'])) {
        if (isset($_POST['sujet']) || isset($_POST['nom']) || isset($_POST['prenom']) || isset($_POST['email']) || isset($_POST['message'])) {
            echo '<script>';
            echo 'var successMessage = document.getElementById("success-message");';
            echo 'if (successMessage !== null) {';
            echo '  successMessage.style.display = "block";';
            echo '}';
            echo '</script>';
        }
    global $wpdb;

    // Get the form data
    $sujet = sanitize_text_field($_POST['sujet']);
    $nom = sanitize_text_field($_POST['nom']);
    $prenom = sanitize_text_field($_POST['prenom']);
    $email = sanitize_email($_POST['email']);
    $message = sanitize_textarea_field($_POST['message']);

    // Insert the form data into the database
    $table_name = $wpdb->prefix . 'contact_form';
    $wpdb->insert(
        $table_name,
        array(
            'sujet' => $sujet,
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'message' => $message,
            'date_envoi' => current_time('mysql')
        ),
        array('%s', '%s', '%s', '%s', '%s', '%s')
    );
  
}

// Create the database table on plugin activation
function contact_form_activation()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'contact_form';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        sujet varchar(255) NOT NULL,
        nom varchar(255) NOT NULL,
        prenom varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        message text NOT NULL,
        date_envoi datetime NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'contact_form_activation');

// Drop the database table on plugin deactivation
function contact_form_deactivation()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'contact_form';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
register_deactivation_hook(__FILE__, 'contact_form_deactivation');




// Add plugin as a side menu item in the admin dashboard
function contact_form_add_menu_item()
{
    add_menu_page(
        'Contact Form Plugin',
        'Contact Form Plugin',
        'manage_options',
        'contact-form-plugin',
        'contact_form_display_responses'
    );
}
add_action('admin_menu', 'contact_form_add_menu_item');

// Display the form submissions in a table
function contact_form_display_responses()
{
    // check if user has permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'contact_form';

    // Check if the delete button has been clicked
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        // Sanitize the input to prevent SQL injection
        $id = intval($_GET['id']);
        // Delete the row from the database
        $wpdb->delete($table_name, array('id' => $id), array('%d'));
    }

    // Get the data from the database
    $data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    // Render the table
    echo '<div class="wrap"><h2>Contact Form Plugin Responses</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr>';
    echo '<th scope="col">Sujet</th>';
    echo '<th scope="col">Nom</th>';
    echo '<th scope="col">Prénom</th>';
    echo '<th scope="col">Email</th>';
    echo '<th scope="col">Message</th>';
    echo '<th scope="col">Date d\'envoi</th>';
    echo '<th scope="col">Actions</th>';
    echo '</tr></thead><tbody>';
    foreach ($data as $row) {
        echo '<tr>';
        echo '<td>' . $row['sujet'] . '</td>';
        echo '<td>' . $row['nom'] . '</td>';
        echo '<td>' . $row['prenom'] . '</td>';
        echo '<td>' . $row['email'] . '</td>';
        echo '<td>' . $row['message'] . '</td>';
        echo '<td>' . $row['date_envoi'] . '</td>';
        echo '<td><a href="?page=contact-form-plugin&action=delete&id=' . $row['id'] . '">Delete</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}




?>






<!-- //[contact_form] -->