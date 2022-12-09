<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrivacyPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $content_en = '<p><p><strong>Last Updated on August 20th, 2020.</strong>&nbsp;This Privacy Policy is effective&nbsp;<strong>August 20th, 2020</strong>&nbsp;for all users. This Privacy Policy describes our policies on the collection, use, and disclosure of information about you in connection with your use of our services, including those offered through our websites, communications (e.g., emails, phone calls, and texts), and mobile applications (collectively, the&nbsp;<strong>&quot;Service&quot;</strong>). The terms&nbsp;<strong>&quot;we&quot;</strong>,&nbsp;<strong>&quot;us&quot;</strong>, and&nbsp;<strong>&quot;Dhaamiye&quot;</strong>&nbsp;refer to: (i) Dhaamiye App; and (ii) Ghima water Company, a company established and resident under the laws of KSA. When you use the Service, you consent to our collection, use, and disclosure of information about you as described in this Privacy Policy. We may translate this Privacy Policy into other languages for your convenience. Nevertheless, the English version governs your relationship with Dhaamiye, and any inconsistencies among the different versions will be resolved in favor of the English version.</p><p>TABLE OF CONTENTS</p><p>1. Information We Collect and How We Use It</p><p>2. Cookies</p><p>3. Third Parties</p><p>4. Controlling Your Personal Data</p><p>5. Data Retention and Account Termination</p><p>6. Children</p><p>7. Security</p><p>8. Contact Information</p><p>9. Modifications to This Privacy Policy</p>';

        $content_so = '<p>Somali: <strong>Last Updated on August 20th, 2020.</strong>&nbsp;This Privacy Policy is effective&nbsp;<strong>August 20th, 2020</strong>&nbsp;for all users. This Privacy Policy describes our policies on the collection, use, and disclosure of information about you in connection with your use of our services, including those offered through our websites, communications (e.g., emails, phone calls, and texts), and mobile applications (collectively, the&nbsp;<strong>&quot;Service&quot;</strong>). The terms&nbsp;<strong>&quot;we&quot;</strong>,&nbsp;<strong>&quot;us&quot;</strong>, and&nbsp;<strong>&quot;Dhaamiye&quot;</strong>&nbsp;refer to: (i) Dhaamiye App; and (ii) Ghima water Company, a company established and resident under the laws of KSA. When you use the Service, you consent to our collection, use, and disclosure of information about you as described in this Privacy Policy. We may translate this Privacy Policy into other languages for your convenience. Nevertheless, the English version governs your relationship with Dhaamiye, and any inconsistencies among the different versions will be resolved in favor of the English version.</p><p>TABLE OF CONTENTS</p><p>1. Information We Collect and How We Use It</p><p>2. Cookies</p><p>3. Third Parties</p><p>4. Controlling Your Personal Data</p><p>5. Data Retention and Account Termination</p><p>6. Children</p><p>7. Security</p><p>8. Contact Information</p><p>9. Modifications to This Privacy Policy</p>';

        $array = array(
            array(
                'title_en' => 'Privacy Policy',
                'title_so' => 'Privacy Policy Somali',
                'policy_en' => $content_en,
                'policy_so'  => $content_so,                
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            )
        );
        DB::table('privacy_policy')->insert($array);
    }
}
