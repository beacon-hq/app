import React from 'react';

interface ObjectDiffProps {
    oldValue?: any;
    newValue: any;
    keyName: string;
}

interface DiffLineProps {
    type: 'added' | 'removed' | 'unchanged';
    keyName: string;
    value: any;
}

function DiffLine({ type, keyName, value }: DiffLineProps) {
    const prefix = type === 'added' ? '++ ' : type === 'removed' ? '-- ' : '   ';
    const colorClass =
        type === 'added' ? 'text-green-600' : type === 'removed' ? 'text-red-600' : 'text-muted-foreground';

    const formatValue = (val: any): string => {
        if (val === null) return 'null';
        if (val === undefined) return 'undefined';
        if (typeof val === 'boolean') return val ? 'true' : 'false';
        if (typeof val === 'string') return `"${val}"`;
        if (typeof val === 'number') return val.toString();
        if (Array.isArray(val)) {
            if (val.length === 0) return '[]';
            if (val.every((item) => typeof item !== 'object' || item === null)) {
                return `[${val.map((item) => (typeof item === 'string' ? `"${item}"` : String(item))).join(', ')}]`;
            }
            return `[${val.length} items]`;
        }
        if (typeof val === 'object') return `{${Object.keys(val).length} keys}`;
        return String(val);
    };

    return (
        <div className={`block ${colorClass}`}>
            {prefix}
            {keyName}: {formatValue(value)}
        </div>
    );
}

function renderObjectDiff(oldObj: any, newObj: any, parentPath: string = ''): JSX.Element[] {
    const elements: JSX.Element[] = [];

    // Handle arrays and objects uniformly by getting all keys/indices
    const getKeys = (obj: any): string[] => {
        if (!obj || typeof obj !== 'object') return [];
        if (Array.isArray(obj)) {
            return obj.map((_, index) => index.toString());
        }
        return Object.keys(obj);
    };

    const allKeys = new Set([...getKeys(oldObj), ...getKeys(newObj)]);

    for (const key of Array.from(allKeys).sort((a, b) => {
        // Sort numeric keys numerically, string keys alphabetically
        const aNum = parseInt(a);
        const bNum = parseInt(b);
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return aNum - bNum;
        }
        return a.localeCompare(b);
    })) {
        const oldValue = oldObj?.[key];
        const newValue = newObj?.[key];
        const hasOld = oldObj && (Array.isArray(oldObj) ? parseInt(key) < oldObj.length : key in oldObj);
        const hasNew = newObj && (Array.isArray(newObj) ? parseInt(key) < newObj.length : key in newObj);
        const fullPath = parentPath ? `${parentPath}.${key}` : key;

        if (!hasOld && hasNew) {
            // Added
            if (typeof newValue === 'object' && newValue !== null) {
                elements.push(...renderObjectDiff(null, newValue, fullPath));
            } else {
                elements.push(<DiffLine key={`${fullPath}-added`} type="added" keyName={fullPath} value={newValue} />);
            }
        } else if (hasOld && !hasNew) {
            // Removed
            if (typeof oldValue === 'object' && oldValue !== null) {
                elements.push(...renderObjectDiff(oldValue, null, fullPath));
            } else {
                elements.push(
                    <DiffLine key={`${fullPath}-removed`} type="removed" keyName={fullPath} value={oldValue} />,
                );
            }
        } else if (hasOld && hasNew) {
            // Compare values
            if (JSON.stringify(oldValue) !== JSON.stringify(newValue)) {
                // Changed
                if (
                    typeof oldValue === 'object' &&
                    oldValue !== null &&
                    typeof newValue === 'object' &&
                    newValue !== null
                ) {
                    // Both are objects/arrays, show nested diff
                    elements.push(...renderObjectDiff(oldValue, newValue, fullPath));
                } else {
                    // Simple value change
                    elements.push(
                        <DiffLine key={`${fullPath}-removed`} type="removed" keyName={fullPath} value={oldValue} />,
                    );
                    elements.push(
                        <DiffLine key={`${fullPath}-added`} type="added" keyName={fullPath} value={newValue} />,
                    );
                }
            } else {
                // Unchanged - show for context
                if (
                    typeof oldValue === 'object' &&
                    oldValue !== null &&
                    typeof newValue === 'object' &&
                    newValue !== null
                ) {
                    // Both are objects/arrays, show nested diff (which may include unchanged nested properties)
                    elements.push(...renderObjectDiff(oldValue, newValue, fullPath));
                } else {
                    // Simple unchanged value
                    elements.push(
                        <DiffLine key={`${fullPath}-unchanged`} type="unchanged" keyName={fullPath} value={newValue} />,
                    );
                }
            }
        }
    }

    return elements;
}

export function ObjectDiff({ oldValue, newValue, keyName }: ObjectDiffProps) {
    // Handle simple value changes (non-objects)
    if (typeof newValue !== 'object' || newValue === null) {
        if (oldValue !== undefined && JSON.stringify(oldValue) !== JSON.stringify(newValue)) {
            return (
                <div>
                    <DiffLine type="removed" keyName={keyName} value={oldValue} />
                    <DiffLine type="added" keyName={keyName} value={newValue} />
                </div>
            );
        } else if (oldValue === undefined) {
            return <DiffLine type="added" keyName={keyName} value={newValue} />;
        }
        return null;
    }

    // Handle object/array changes
    if (oldValue === undefined) {
        // New object/array - show all nested properties as added
        return <div>{renderObjectDiff(null, newValue, keyName)}</div>;
    }

    if (typeof oldValue === 'object' && oldValue !== null) {
        // Object/array to object/array comparison
        const diffElements = renderObjectDiff(oldValue, newValue, keyName);
        if (diffElements.length === 0) return null;

        return <div>{diffElements}</div>;
    }

    // Old value was not an object/array, but new value is
    return (
        <div>
            <DiffLine type="removed" keyName={keyName} value={oldValue} />
            {renderObjectDiff(null, newValue, keyName)}
        </div>
    );
}
