/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  darkMode: 'class',
  theme: {
    extend: {
       colors: {
           brand: {
              blue: '#111827',
              blueDeep: '#030712',
              cyan: '#6b7280',
              surface: '#f5f5f5',
              panel: '#ffffff',
              text: '#111827',
              muted: '#6b7280',
              line: '#e5e7eb',
              darkBg: '#111827',
              darkPanel: '#1f2937',
              darkLine: '#374151',
              // Standard vibrant blue (for login & fallback)
              vibrantBlue: '#4C6FFF',
              vibrantBlueDeep: '#3E58F4',
              navy: '#161B67',
              navySoft: '#242A7B',
              surfaceSoft: '#F4F6FB',
              surfaceDark: '#091127',
              cardDark: '#0F1730',
              // Login specific variants
              loginText: '#1D2433',
              loginMuted: '#7A8599',
              loginLine: '#E3E8F2',
              loginLineDark: '#24304B',
           }
       },
       boxShadow: {
          soft: '0 12px 30px rgba(15, 23, 42, 0.06)',
          float: '0 12px 28px rgba(17, 24, 39, 0.16)'
       }
    },
  },
  plugins: [],
}
