<?php

declare(strict_types=1);

final class DiscordRoleManager
{
    private string $botToken;
    private string $guildId;
    private string $verifiedRoleId;

    public function __construct(string $botToken, string $guildId, string $verifiedRoleId)
    {
        $this->botToken = trim($botToken);
        $this->guildId = trim($guildId);
        $this->verifiedRoleId = trim($verifiedRoleId);

        if ($this->botToken === '' || $this->guildId === '' || $this->verifiedRoleId === '') {
            throw new RuntimeException('Discord role manager is missing bot_token, guild_id, or verified_role_id.');
        }
    }

    public function assignVerifiedRole(string $discordUserId, string $userAccessToken): void
    {
        $discordUserId = trim($discordUserId);
        $userAccessToken = trim($userAccessToken);

        if ($discordUserId === '' || $userAccessToken === '') {
            throw new RuntimeException('Discord user id or access token is missing.');
        }

        $this->discordRequest(
            'PUT',
            sprintf('/guilds/%s/members/%s', rawurlencode($this->guildId), rawurlencode($discordUserId)),
            [
                'Authorization: Bot ' . $this->botToken,
                'Content-Type: application/json',
            ],
            [
                'access_token' => $userAccessToken,
            ],
            [201, 204]
        );

        $this->discordRequest(
            'PUT',
            sprintf(
                '/guilds/%s/members/%s/roles/%s',
                rawurlencode($this->guildId),
                rawurlencode($discordUserId),
                rawurlencode($this->verifiedRoleId)
            ),
            [
                'Authorization: Bot ' . $this->botToken,
            ],
            null,
            [204]
        );
    }

    private function discordRequest(string $method, string $path, array $headers, ?array $jsonPayload, array $expectedStatuses): array
    {
        $ch = curl_init('https://discord.com/api/v10' . $path);
        if ($ch === false) {
            throw new RuntimeException('Failed to initialize Discord API request.');
        }

        $options = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 15,
        ];

        if ($jsonPayload !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($jsonPayload, JSON_THROW_ON_ERROR);
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Discord API request failed: ' . $error);
        }

        if (!in_array($status, $expectedStatuses, true)) {
            throw new RuntimeException(sprintf('Discord API returned HTTP %d: %s', $status, $response));
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : [];
    }
}
