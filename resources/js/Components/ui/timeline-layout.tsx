'use client';

import { Timeline, TimelineItem } from './timeline';
import { Color } from '@/Application';
import { TimelineElement } from '@/types/timeline';
import { motion } from 'framer-motion';
import React from 'react';

interface TimelineLayoutProps {
    items: TimelineElement[];
    size?: 'sm' | 'md' | 'lg';
    iconColor?: 'primary' | 'secondary' | 'muted' | 'accent' | Color;
    customIcon?: React.ReactNode;
    animate?: boolean;
    connectorColor?: 'primary' | 'secondary' | 'muted' | 'accent' | Color;
    className?: string;
}

export const TimelineLayout = ({
    items,
    size = 'md',
    iconColor,
    customIcon,
    animate = true,
    connectorColor,
    className,
}: TimelineLayoutProps) => {
    return (
        <Timeline size={size} className={className}>
            {[...items].map((item, index) => (
                <motion.div
                    key={index}
                    initial={animate ? { opacity: 0, y: 20 } : false}
                    animate={animate ? { opacity: 1, y: 0 } : false}
                    transition={{
                        duration: 0.5,
                        delay: index * 0.1,
                        ease: 'easeOut',
                    }}
                >
                    <TimelineItem
                        date={item.date}
                        title={item.title}
                        description={item.description}
                        icon={typeof item.icon === 'function' ? item.icon() : item.icon || customIcon}
                        iconColor={item.color || iconColor}
                        connectorColor={item.color || connectorColor}
                        showConnector={index !== items.length - 1}
                    />
                </motion.div>
            ))}
        </Timeline>
    );
};
