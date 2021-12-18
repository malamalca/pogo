<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\IdentityInterface;
use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $username
 * @property string|null $passwd
 * @property string|null $email
 * @property string|null $reset_key
 * @property int $privileges
 * @property bool $active
 * @property string|null $avatar
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 */
class User extends Entity implements IdentityInterface
{
    public const ROLE_ROOT = 2;
    public const PROJECT_ROLE_ADMIN = 5;
    public const PROJECT_ROLE_EDITOR = 5;

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'name' => true,
        'username' => true,
        'passwd' => true,
        'email' => true,
        'reset_key' => true,
        'privileges' => true,
        'active' => true,
        'created' => true,
        'modified' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'passwd',
    ];

    /**
     * Entity to string magic method
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * Set password method.
     *
     * @param string $password Users password.
     * @return string|bool
     */
    protected function _setPasswd($password)
    {
        return (new DefaultPasswordHasher())->hash($password);
    }

    /**
     * Authentication\IdentityInterface method
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->id;
    }

    /**
     * Authentication\IdentityInterface method
     *
     * @return \App\Model\Entity\User
     */
    public function getOriginalData()
    {
        return $this;
    }

    /**
     * Checks user's role.
     *
     * @param string $role User role.
     * @param string|null $projectId  Project id.
     * @return bool
     */
    public function hasRole($role, $projectId = null)
    {
        if ($role == 'root') {
            return $this->privileges <= self::ROLE_ROOT;
        }
        if ($role == 'admin') {
            return $this->privileges <= self::PROJECT_ROLE_ADMIN;
        }
        if ($role == 'editor') {
            return $this->privileges <= self::PROJECT_ROLE_EDITOR;
        }

        return true;
    }

    /**
     * Returns users avatar as image
     *
     * @return string|bool
     */
    public function getAvatarImage()
    {
        $ret = false;
        $avatarSize = 90;

        if (!empty($this->avatar)) {
            $im = imagecreatefromstring(base64_decode($this->avatar));
            $width = imagesx($im);
            $height = imagesy($im);

            if ($width > $height) {
                $newHeight = $avatarSize;
                $newWidth = (int)floor($width * $newHeight / $height);
                $cropX = (int)ceil(($width - $height) / 2);
                $cropY = 0;
            } else {
                $newWidth = $avatarSize;
                $newHeight = (int)floor($height * $newWidth / $width);
                $cropX = 0;
                $cropY = (int)ceil(($height - $width) / 2);
            }

            $newImage = imagecreatetruecolor($avatarSize, $avatarSize);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $avatarSize, $avatarSize, $transparent);
            imagecopyresampled($newImage, $im, 0, 0, $cropX, $cropY, $newWidth, $newHeight, $width, $height);
            imagedestroy($im);

            ob_start();
            imagepng($newImage);
            $ret = ob_get_contents();
            ob_end_clean();
            imagedestroy($newImage);
        }

        return $ret;
    }
}
