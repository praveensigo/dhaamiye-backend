<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $content_en = '<p><strong>Last Updated on August 20th, 2021.</strong>&nbsp;This Privacy Policy is effective&nbsp;<strong>August 20th, 2021</strong>&nbsp;for all users. This Privacy Policy describes our policies on the collection, use, and disclosure of information about you in connection with your use of our services, including those offered through our websites, communications (e.g., emails, phone calls, and texts), and mobile applications (collectively, the&nbsp;<strong>&quot;Service&quot;</strong>). The terms&nbsp;<strong>&quot;we&quot;</strong>,&nbsp;<strong>&quot;us&quot;</strong>, and&nbsp;<strong>&quot;Dhaamiye&quot;</strong>&nbsp;refer to: (i) Dhaamiye App; and (ii) Ghima water Company, a company established and resident under the laws of KSA. When you use the Service, you consent to our collection, use, and disclosure of information about you as described in this Privacy Policy. We may translate this Privacy Policy into other languages for your convenience. Nevertheless, the English version governs your relationship with Dhaamiye, and any inconsistencies among the different versions will be resolved in favor of the English version.</p><p>TABLE OF CONTENTS</p><p>1. Information We Collect and How We Use It</p><p>2. Cookies</p><p>3. Third Parties</p><p>4. Controlling Your Personal Data</p><p>5. Data Retention and Account Termination</p><p>6. Children</p><p>7. Security</p><p>8. Contact Information</p><p>9. Modifications to This Privacy Policy</p><p>1. INFORMATION WE COLLECT AND HOW WE USE IT</p><p>We may collect, transmit, and store information about you in connection with your use of the Service, including any information you send to or through the Service. We use that information to provide the Service&#39;s functionality, fulfill your requests, improve the Service&#39;s quality, engage in research and analysis relating to the Service, personalize your experience, track usage of the Service, provide feedback to third party businesses that are listed on the Service, display relevant advertising, market the Service, provide customer support, message you, back up our systems, allow for disaster recovery, enhance the security of the Service, and comply with legal obligations. Even when we do not retain such information, it still must be transmitted to our servers initially and stored long enough to process.</p>';

        $content_so = '<p>Somali: <strong>Last Updated on August 20th, 2021.</strong>&nbsp;This Privacy Policy is effective&nbsp;<strong>August 20th, 2021</strong>&nbsp;for all users. This Privacy Policy describes our policies on the collection, use, and disclosure of information about you in connection with your use of our services, including those offered through our websites, communications (e.g., emails, phone calls, and texts), and mobile applications (collectively, the&nbsp;<strong>&quot;Service&quot;</strong>). The terms&nbsp;<strong>&quot;we&quot;</strong>,&nbsp;<strong>&quot;us&quot;</strong>, and&nbsp;<strong>&quot;Dhaamiye&quot;</strong>&nbsp;refer to: (i) Dhaamiye App; and (ii) Ghima water Company, a company established and resident under the laws of KSA. When you use the Service, you consent to our collection, use, and disclosure of information about you as described in this Privacy Policy. We may translate this Privacy Policy into other languages for your convenience. Nevertheless, the English version governs your relationship with Dhaamiye, and any inconsistencies among the different versions will be resolved in favor of the English version.</p><p>TABLE OF CONTENTS</p><p>1. Information We Collect and How We Use It</p><p>2. Cookies</p><p>3. Third Parties</p><p>4. Controlling Your Personal Data</p><p>5. Data Retention and Account Termination</p><p>6. Children</p><p>7. Security</p><p>8. Contact Information</p><p>9. Modifications to This Privacy Policy</p><p>1. INFORMATION WE COLLECT AND HOW WE USE IT</p><p>We may collect, transmit, and store information about you in connection with your use of the Service, including any information you send to or through the Service. We use that information to provide the Service&#39;s functionality, fulfill your requests, improve the Service&#39;s quality, engage in research and analysis relating to the Service, personalize your experience, track usage of the Service, provide feedback to third party businesses that are listed on the Service, display relevant advertising, market the Service, provide customer support, message you, back up our systems, allow for disaster recovery, enhance the security of the Service, and comply with legal obligations. Even when we do not retain such information, it still must be transmitted to our servers initially and stored long enough to process.</p>';

        $array = array(
            array(
                'title_en' => 'Terms & Conditions',
                'title_so' => 'Terms & Conditions Somali',
                'term_en' => $content_en,
                'term_so'  => $content_so,                
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            )
        );
        DB::table('terms')->insert($array);
    }
}
