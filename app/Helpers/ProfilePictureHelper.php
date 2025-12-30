<?php

namespace App\Helpers;

class ProfilePictureHelper
{
    /**
     * Get the appropriate profile picture URL or asset path
     *
     * @param string|null $profilePicture
     * @return string|null
     */
    public static function getProfilePictureUrl(?string $profilePicture): ?string
    {
        if (empty($profilePicture)) {
            return null;
        }

        // If it's already a full URL (like Google profile picture), return as is
        if (str_starts_with($profilePicture, 'http')) {
            return $profilePicture;
        }

        // Otherwise, treat it as a local storage file
        return asset('storage/' . $profilePicture);
    }

    /**
     * Check if the profile picture is an external URL
     *
     * @param string|null $profilePicture
     * @return bool
     */
    public static function isExternalProfilePicture(?string $profilePicture): bool
    {
        return !empty($profilePicture) && str_starts_with($profilePicture, 'http');
    }
}
