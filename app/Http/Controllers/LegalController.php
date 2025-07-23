<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class LegalController extends Controller
{
    /**
     * Show the Terms of Service page.
     */
    public function terms(): View
    {
        return view('legal.terms', [
            'title' => 'Terms of Service',
            'lastUpdated' => now()->format('F d, Y'),
            'companyName' => config('app.name', 'Monetro.io')
        ]);
    }

    /**
     * Show the Privacy Policy page.
     */
    public function privacy(): View
    {
        return view('legal.privacy', [
            'title' => 'Privacy Policy',
            'lastUpdated' => now()->format('F d, Y'),
            'companyName' => config('app.name', 'Monetro.io')
        ]);
    }

    /**
     * Get Terms of Service content for modal (AJAX)
     */
    public function getTermsModal(): JsonResponse
    {
        $companyName = config('app.name', 'Monetro.io');
        $lastUpdated = now()->format('F d, Y');

        $content = [
            'title' => 'Terms of Service',
            'lastUpdated' => $lastUpdated,
            'companyName' => $companyName,
            'sections' => $this->getTermsSections($companyName)
        ];

        return response()->json($content);
    }

    /**
     * Get Privacy Policy content for modal (AJAX)
     */
    public function getPrivacyModal(): JsonResponse
    {
        $companyName = config('app.name', 'Monetro.io');
        $lastUpdated = now()->format('F d, Y');

        $content = [
            'title' => 'Privacy Policy',
            'lastUpdated' => $lastUpdated,
            'companyName' => $companyName,
            'sections' => $this->getPrivacySections($companyName)
        ];

        return response()->json($content);
    }

    /**
     * Record user acceptance of legal documents
     */
    public function recordAcceptance(Request $request): JsonResponse
    {
        $request->validate([
            'document_type' => 'required|in:terms,privacy',
            'accepted' => 'required|boolean'
        ]);

        if (auth()->check()) {
            // Update user record with acceptance timestamp
            $user = auth()->user();
            
            if ($request->document_type === 'terms') {
                $user->terms_accepted_at = $request->accepted ? now() : null;
            } else {
                $user->privacy_accepted_at = $request->accepted ? now() : null;
            }
            
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->document_type) . ' acceptance recorded successfully'
        ]);
    }

    /**
     * Get Terms of Service sections content
     */
    private function getTermsSections(string $companyName): array
    {
        return [
            [
                'title' => '1. Introduction',
                'content' => "Welcome to {$companyName} (\"we,\" \"our,\" or \"us\"). These Terms of Service (\"Terms\") govern your use of our personal finance management platform and services (the \"Service\") operated by {$companyName}.<br><br>By accessing or using our Service, you agree to be bound by these Terms. If you disagree with any part of these terms, then you may not access the Service."
            ],
            [
                'title' => '2. Acceptance of Terms',
                'content' => "By creating an account or using {$companyName}, you acknowledge that you have read, understood, and agree to be bound by these Terms and our Privacy Policy.<br><br>• You must be at least 18 years old to use this Service<br>• You must provide accurate and complete information<br>• You are responsible for maintaining the security of your account"
            ],
            [
                'title' => '3. Description of Service',
                'content' => "{$companyName} provides a comprehensive personal finance management platform that allows users to:<br><br>• Track income and expenses<br>• Monitor investment portfolios<br>• Set and track financial goals<br>• Analyze spending patterns<br>• Access financial insights and reports"
            ],
            [
                'title' => '4. User Accounts',
                'content' => "<strong>4.1 Account Creation</strong><br>To access certain features, you must create an account. You agree to provide accurate, current, and complete information during registration.<br><br><strong>4.2 Account Security</strong><br>You are responsible for:<br>• Maintaining the confidentiality of your account credentials<br>• All activities that occur under your account<br>• Immediately notifying us of any unauthorized access<br><br><strong>4.3 Account Termination</strong><br>We reserve the right to terminate or suspend your account at our discretion, with or without notice, for violations of these Terms."
            ],
            [
                'title' => '5. Financial Data and Privacy',
                'content' => "<strong>5.1 Data Collection</strong><br>We collect and process financial data you provide to deliver our services. This includes transaction data, account balances, and investment information.<br><br><strong>5.2 Data Security</strong><br>We implement industry-standard security measures to protect your financial data, including:<br>• 256-bit encryption for data transmission<br>• Secure data storage with bank-level security<br>• Regular security audits and updates<br>• Limited access controls for our staff<br><br><strong>5.3 Data Usage</strong><br>Your financial data is used exclusively to provide our services. We do not sell your personal financial information to third parties."
            ],
            [
                'title' => '6. Limitation of Liability',
                'content' => "In no event shall {$companyName}, its directors, employees, or agents be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses."
            ]
        ];
    }

    /**
     * Get Privacy Policy sections content
     */
    private function getPrivacySections(string $companyName): array
    {
        return [
            [
                'title' => '1. Information We Collect',
                'content' => "We collect information you provide directly to us, such as when you create an account, use our services, or contact us. This includes:<br><br>• Personal information (name, email, phone number)<br>• Financial data (transaction history, account balances)<br>• Device and usage information<br>• Communication preferences"
            ],
            [
                'title' => '2. How We Use Your Information',
                'content' => "We use the information we collect to:<br><br>• Provide and maintain our services<br>• Process transactions and send notifications<br>• Improve our platform and develop new features<br>• Communicate with you about our services<br>• Ensure security and prevent fraud"
            ],
            [
                'title' => '3. Data Protection',
                'content' => "We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:<br><br>• Encryption of data in transit and at rest<br>• Regular security assessments<br>• Access controls and authentication<br>• Employee training on data protection"
            ],
            [
                'title' => '4. Data Sharing',
                'content' => "We do not sell, rent, or share your personal information with third parties except:<br><br>• With your explicit consent<br>• To comply with legal obligations<br>• To protect our rights and safety<br>• With trusted service providers who assist our operations"
            ],
            [
                'title' => '5. Your Rights',
                'content' => "You have the right to:<br><br>• Access your personal information<br>• Correct or update your data<br>• Delete your account and data<br>• Object to processing<br>• Data portability<br>• Withdraw consent at any time"
            ],
            [
                'title' => '6. Contact Us',
                'content' => "If you have questions about this Privacy Policy, please contact us at:<br><br>Email: privacy@monetro.io<br>Address: 456 Digital Park, Mueang Chiang Mai, Chiang Mai 50000, Thailand<br>Phone: +66 53 123 456"
            ]
        ];
    }
}