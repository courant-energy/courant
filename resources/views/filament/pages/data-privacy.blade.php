<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Data Privacy Information -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Data Privacy & Protection</h2>
                
                <div class="prose dark:prose-invert max-w-none">
                    <h3 class="text-lg font-semibold mb-3">What Data We Collect</h3>
                    <ul class="list-disc pl-6 space-y-2 mb-6">
                        <li><strong>Account Information:</strong> Your name, email address, company name, and profile picture</li>
                        <li><strong>IoT Device Data:</strong> Device configurations, energy readings, power consumption data</li>
                        <li><strong>Usage Analytics:</strong> How you interact with our platform for service improvement</li>
                        <li><strong>Support Data:</strong> Questions and support tickets you submit</li>
                        <li><strong>System Logs:</strong> Technical logs for troubleshooting and security purposes</li>
                    </ul>

                    <h3 class="text-lg font-semibold mb-3">How We Protect Your Data</h3>
                    <ul class="list-disc pl-6 space-y-2 mb-6">
                        <li>All data is encrypted in transit and at rest</li>
                        <li>Access is restricted to authorized personnel only</li>
                        <li>Regular security audits and monitoring</li>
                        <li>Compliance with data protection regulations</li>
                    </ul>

                    <h3 class="text-lg font-semibold mb-3">Your Data Rights</h3>
                    <ul class="list-disc pl-6 space-y-2 mb-6">
                        <li><strong>Access:</strong> Request a copy of your personal data</li>
                        <li><strong>Rectification:</strong> Correct inaccurate or incomplete data</li>
                        <li><strong>Erasure:</strong> Request deletion of your personal data</li>
                        <li><strong>Portability:</strong> Export your data in a machine-readable format</li>
                        <li><strong>Restriction:</strong> Limit how we process your data</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Account Deletion Information -->
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold text-red-800 dark:text-red-200 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    Account Deletion & Data Wiping
                </h2>
                
                <div class="text-red-700 dark:text-red-300">
                    <p class="mb-4">
                        If you wish to permanently delete your account and all associated data, you can use the "Delete My Account" button above. 
                        This action will completely remove:
                    </p>
                    
                    <div class="grid md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <h4 class="font-semibold mb-2">Personal Data:</h4>
                            <ul class="list-disc pl-5 space-y-1 text-sm">
                                <li>Your user account and profile</li>
                                <li>Contact information</li>
                                <li>Profile pictures and uploads</li>
                                <li>Account preferences</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold mb-2">IoT & Energy Data:</h4>
                            <ul class="list-disc pl-5 space-y-1 text-sm">
                                <li>All IoT device configurations</li>
                                <li>Historical energy readings</li>
                                <li>Power consumption analytics</li>
                                <li>Device monitoring profiles</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold mb-2">Support & Communication:</h4>
                            <ul class="list-disc pl-5 space-y-1 text-sm">
                                <li>Support tickets and questions</li>
                                <li>System notifications</li>
                                <li>Feedback and responses</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold mb-2">System Data:</h4>
                            <ul class="list-disc pl-5 space-y-1 text-sm">
                                <li>Usage logs and analytics</li>
                                <li>System alerts and events</li>
                                <li>Backup and recovery data</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="bg-red-100 dark:bg-red-900/40 border border-red-300 dark:border-red-700 rounded p-4">
                        <p class="font-semibold text-red-800 dark:text-red-200 mb-2">⚠️ Important Security Measures:</p>
                        <ul class="list-disc pl-5 space-y-1 text-sm text-red-700 dark:text-red-300">
                            <li>Multiple confirmation steps required</li>
                            <li>Email address verification</li>
                            <li>Password confirmation</li>
                            <li>Explicit confirmation text required</li>
                            <li>Action is logged for security purposes</li>
                            <li><strong>This action cannot be undone</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold text-blue-800 dark:text-blue-200 mb-4">Need Help?</h2>
                <p class="text-blue-700 dark:text-blue-300 mb-3">
                    If you have questions about data privacy, need to exercise your data rights, or require assistance with account management, please contact our support team.
                </p>
                <div class="text-blue-700 dark:text-blue-300 text-sm">
                    <p><strong>Response Time:</strong> We typically respond within 24-48 hours</p>
                    <p><strong>Data Requests:</strong> Allow up to 30 days for processing</p>
                </div>
            </div>
        </div>
    </div>

    {{-- delete action --}}
    <div class="mt-6">
        {{ $this->deleteAccountAction }}
    </div>
</x-filament-panels::page>
