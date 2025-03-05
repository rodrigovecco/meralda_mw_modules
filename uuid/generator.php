<?php
class mwmod_mw_uuid_generator{
    /**
     * Generate a version 4 UUID.
     *
     * @return string The generated UUID.
     */
    public static function generateV4()    {
        // Generate 16 bytes (128 bits) of random data
        $data = random_bytes(16);

        // Set the version to 0100 (UUIDv4)
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);

        // Set the variant to 10xx
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Convert the binary data into a hexadecimal string with hyphens
        return sprintf(
            '%08x-%04x-%04x-%04x-%012x',
            // 32 bits for "time_low"
            bin2hex(substr($data, 0, 4)),
            // 16 bits for "time_mid"
            bin2hex(substr($data, 4, 2)),
            // 16 bits for "time_hi_and_version"
            bin2hex(substr($data, 6, 2)),
            // 16 bits for "clk_seq_hi_res" and "clk_seq_low"
            bin2hex(substr($data, 8, 2)),
            // 48 bits for "node"
            bin2hex(substr($data, 10, 6))
        );
    }

    /**
     * Generate a UUID using the current time to minimize duplication.
     *
     * @return string The generated time-based UUID.
     */
    public static function generateTimeBased(){
        // Get the current timestamp in microseconds
        $time = microtime(true);

        // Split the time into seconds and microseconds
        $seconds = (int) $time;
        $microseconds = (int) (($time - $seconds) * 1000000);

        // Create a unique identifier using the time and a random component
        return sprintf(
            '%08x-%04x-%04x-%04x-%012x',
            $seconds & 0xffffffff, // 32 bits for time_low
            ($microseconds >> 16) & 0xffff, // 16 bits for time_mid
            ($microseconds & 0xffff) | 0x1000, // 16 bits for time_hi_and_version with version 1
            mt_rand(0x8000, 0xbfff), // 16 bits for clk_seq_hi_res and clk_seq_low
            mt_rand(0, 0xffffffffffff) // 48 bits for node
        );
    }
}
?>