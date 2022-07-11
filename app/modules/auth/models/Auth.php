<?php
namespace modules\auth\models;
class Auth extends \Model {

    public function attempt_login($user, $password) {

        //check if user
        $existing_data = $this->does_user_exist($user, $user);
        if(empty($existing_data)) {
            return 'Invalid username or password.';
        }
        if(count($existing_data) > 1) {
            //multiple accounts exist for user/email
            return 'Something has gone terribly wrong. Please contact the developer.';
        }

        return $this->login_user($existing_data[0], $password);

    }

	public function create_account($display_name, $email, $password) {

        //check if username or email exists. Return an error if it does.
        $existing_data = $this->does_user_exist($display_name, $email);
        if(!empty($existing_data)) {
            return 'The given display name or email already belongs to another account.';
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

		$this->db->run('INSERT INTO users (display_name, email, password) VALUES (?, ?, ?)', [$display_name, $email ?: null, $hashed_password]);

        //login the user, because account creation was successful.
        return $this->login_user($this->db->lastInsertId(), $password);
	}

    private function does_user_exist($display_name, $email) {
        return $this->db->column('SELECT uid FROM users WHERE display_name=? OR email=?', [$display_name, $email]);
    }

    private function login_user($uid, $password) {

        $user = $this->db->row('SELECT * FROM users WHERE uid=?', [$uid]);

        if(password_verify($password, $user['password'])) {
            unset($user['password']);
            $_SESSION['user'] = $user;
            return;
        } else {
            return 'The given username/email or password does not match our records.';
        }

    }

}
