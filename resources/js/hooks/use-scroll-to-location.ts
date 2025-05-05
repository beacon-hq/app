import { useEffect, useRef } from 'react';

const useScrollToLocation = () => {
    const scrolledRef = useRef(false);
    const { hash } = window.location;
    const hashRef = useRef(hash);

    useEffect(() => {
        if (hash) {
            // We want to reset if the hash has changed
            if (hashRef.current !== hash) {
                hashRef.current = hash;
                scrolledRef.current = false;
            }

            // only attempt to scroll if we haven't yet (this could have just reset above if hash changed)
            if (!scrolledRef.current) {
                const id = hash.replace('#', '');
                const element = document.getElementById(id);
                if (element) {
                    const elementPosition = element.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - 40;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth',
                    });
                    scrolledRef.current = true;
                }
            }
        }
    });
};

export default useScrollToLocation;
