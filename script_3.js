
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: [
                            '-apple-system', 'BlinkMacSystemFont', '"SF Pro Text"', '"SF Pro Display"',
                            'Inter', 'system-ui', 'sans-serif'
                        ],
                        mono: [
                            '"JetBrains Mono"', '"SF Mono"', 'Menlo', 'Monaco', 'monospace'
                        ],
                    },
                    colors: {
                        apple: {
                            blue: '#007AFF',
                            'blue-dark': '#0A84FF',
                            green: '#34C759',
                            'green-dark': '#30D158',
                            orange: '#FF9500',
                            'orange-dark': '#FF9F0A',
                            red: '#FF3B30',
                            'red-dark': '#FF453A',
                            purple: '#AF52DE',
                            'purple-dark': '#BF5AF2',
                            teal: '#30B0C7',
                            'teal-dark': '#40C8E0',
                            indigo: '#5856D6',
                            'indigo-dark': '#5E5CE6',
                            yellow: '#FFCC00',
                            'yellow-dark': '#FFD60A',
                            gray: '#8E8E93',
                            gray2: '#AEAEB2',
                            gray3: '#C7C7CC',
                            gray4: '#D1D1D6',
                            gray5: '#E5E5EA',
                            gray6: '#F2F2F7',
                        },
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae0fd',
                            300: '#7cc5fb',
                            400: '#36a6f7',
                            500: '#007AFF',
                            600: '#0071E3',
                            700: '#0056b3',
                            800: '#00448f',
                            900: '#00336d',
                            950: '#001e44',
                        }
                    },
                    borderRadius: {
                        'squircle-sm': 'var(--r-sm, 8px)',
                        'squircle-md': 'var(--r-md, 10px)',
                        'squircle-lg': 'var(--r-lg, 14px)',
                        'squircle-xl': 'var(--r-xl, 16px)',
                        'squircle-2xl': 'var(--r-2xl, 20px)',
                    }
                }
            }
        }
    