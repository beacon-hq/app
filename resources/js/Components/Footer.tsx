import React from 'react';

const Footer = () => {
    return (
        <footer className="text-center text-neutral-400 bg-background w-full text-xs py-4">
            <p>Made with 🦁💖🏳️‍🌈 by Davey Shafik.</p>
            <p>
                Released under the <a href="https://github.com/beacon-hq/app/blob/main/LICENSE.md">FCL-1.0-MIT</a>{' '}
                License. Copyright © 2024-{new Date().getFullYear()} Davey Shafik.
            </p>
        </footer>
    );
};

export default Footer;
