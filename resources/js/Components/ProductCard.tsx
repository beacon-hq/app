import { Product, Subscription } from '@/Application';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/Components/ui/alert-dialog';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { cn } from '@/lib/utils';
import React from 'react';

function ProductCard(props: { product: Product; activeSubscription?: Subscription; onClick: () => void }) {
    return (
        <Card
            className={cn(
                'text-center relative flex flex-col justify-between',
                'hover:[background:linear-gradient(45deg,#fff,theme(colors.white)_50%,#fff)_padding-box,conic-gradient(from_var(--border-angle),#07e38f,#00e7aa,#13daf4,#07baf9,_theme(colors.sky.700/.48))_border-box] rounded-2xl border-4 border-transparent motion-safe:animate-border',
                'hover:motion-safe:scale-110',
                'transition-all duration-500',
                'relative',
            )}
        >
            <CardContent className="rounded-xl shadow-lg bg-secondary">
                <CardHeader>
                    {props.product.entitlements.trial_length &&
                        !props.activeSubscription &&
                        !props.activeSubscription && (
                            <div className="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-[#07e38f] text-white text-xs font-bold uppercase scale-120 px-3 py-1 rounded-full">
                                {props.product.entitlements.trial_length} Free
                            </div>
                        )}
                    {props.activeSubscription && props.activeSubscription.plan.id === props.product.id && (
                        <div className="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-[#07e38f] text-white text-xs font-bold uppercase scale-120 px-3 py-1 rounded-full">
                            Current Plan
                        </div>
                    )}
                    <CardTitle>
                        <h2 className="text-xl font-semibold text-primary">{props.product.name}</h2>
                    </CardTitle>
                </CardHeader>
                <CardDescription>
                    <p className="mt-2 text-3xl font-bold text-primary">{props.product.base_price}/mo</p>
                    <p className="mt-2 text-primary">
                        {props.product.entitlements.evaluations < 1000000 &&
                            props.product.entitlements.evaluations.toLocaleString()}
                        {props.product.entitlements.evaluations >= 1000000 &&
                            (props.product.entitlements.evaluations / 1000000).toLocaleString() + 'M'}{' '}
                        feature flag evaluations
                    </p>
                    <p className="mt-2 text-primary/60 text-sm">
                        {props.product.metered_price}/{props.product.metadata.evaluation_tier_size.toLocaleString()}{' '}
                        evaluations after that
                    </p>
                    {props.activeSubscription ? (
                        <AlertDialog>
                            <AlertDialogTrigger asChild>
                                <Button
                                    className="mt-4 cursor-pointer"
                                    disabled={
                                        props.activeSubscription &&
                                        props.activeSubscription.plan.id === props.product.id
                                    }
                                >
                                    Change Plan
                                </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                                <AlertDialogHeader>
                                    <AlertDialogTitle>Change Subscription?</AlertDialogTitle>
                                    <AlertDialogDescription>
                                        Are you sure you want to change your subscription to the {props.product.name}{' '}
                                        plan? This will take effect immediately.
                                    </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                    <AlertDialogAction className="bg-primary hover:bg-primary text-primary cursor-pointer">
                                        <Button
                                            variant="default"
                                            className="cursor-pointer"
                                            onClick={() => props.onClick()}
                                        >
                                            Change Plan
                                        </Button>
                                    </AlertDialogAction>
                                </AlertDialogFooter>
                            </AlertDialogContent>
                        </AlertDialog>
                    ) : (
                        <a href={`/register?plan=${props.product.id}`}>
                            <span className="absolute inset-0"></span>
                            <Button className="mt-4 cursor-pointer">
                                {props.product.entitlements.trial_length && 'Start Free Trial'}
                                {!props.product.entitlements.trial_length && 'Choose Plan'}
                            </Button>
                        </a>
                    )}
                </CardDescription>
            </CardContent>
        </Card>
    );
}

export default ProductCard;
