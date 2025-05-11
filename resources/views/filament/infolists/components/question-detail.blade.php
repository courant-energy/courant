<div class="prose prose-sm sm:prose lg:prose-lg xl:prose-xl dark:prose-invert max-w-full overflow-x-auto break-words">
    <style>
        .rich-text-content img {
            max-width: 100%;
            height: auto;
        }
        .rich-text-content pre {
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .rich-text-content table {
            width: 100%;
            display: block;
            overflow-x: auto;
        }
        @media (max-width: 640px) {
            .rich-text-content {
                font-size: 0.9rem;
            }
        }
        
        /* Dark mode specific styling */
        @media (prefers-color-scheme: dark) {
            .rich-text-content {
                color: #e2e8f0;
            }
            .rich-text-content a {
                color: #93c5fd;
            }
            .rich-text-content code {
                background-color: #1e293b;
                color: #e2e8f0;
            }
        }
    </style>
    <div class="rich-text-content">
        {!! $state !!}
    </div>
</div>
