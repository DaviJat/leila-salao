import { definePreset } from '@primevue/themes';
import Aura from '@primevue/themes/aura';

const MyPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: '#f4f6f3',
            100: '#e7ece5',
            200: '#cfdacd',
            300: '#abc0a7',
            400: '#83a07e',
            500: '#5a7253',
            600: '#4a5e44',
            700: '#3c4b38',
            800: '#323e2f',
            900: '#2a3428',
            950: '#161c15'
        },
        colorScheme: {
            light: {
                primary: {
                    color: '{primary.500}',
                    inverseColor: '#ffffff',
                    hoverColor: '{primary.600}',
                    activeColor: '{primary.700}'
                },
                surface: {
                    0: '#ffffff',
                    50: '#fafaf9',
                    100: '#f4f4f3',
                    200: '#e8e8e6',
                    300: '#d1d1ce',
                    400: '#a2a29e',
                    500: '#7e7e7a',
                    600: '#646461',
                    700: '#51514e',
                    800: '#42423f',
                    900: '#373735',
                    950: '#1d1d1c'
                }
            }
        }
    }
});

export default MyPreset;