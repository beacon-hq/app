// @ts-check
import reactHooks from 'eslint-plugin-react-hooks';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    // eslint.configs.recommended,
    tseslint.configs.recommended,
    reactHooks.configs['recommended-latest'],
);
