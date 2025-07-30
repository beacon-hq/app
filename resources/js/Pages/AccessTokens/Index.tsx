import { AccessTokenCollection } from '@/Application';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Dialog, DialogClose, DialogContent, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Toaster } from '@/Components/ui/sonner';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import Table from '@/Pages/AccessTokens/Components/Table';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import { AxiosError } from 'axios';
import { PlusCircle } from 'lucide-react';
import React, { FormEvent, useState } from 'react';
import { toast } from 'sonner';

export default function Index({ settings }: PageProps & { settings: { tokens: AccessTokenCollection } }) {
    const [showTokenDialog, setShowTokenDialog] = useState(false);
    const [processing, setProcessing] = useState(false);
    const [tokenName, setTokenName] = useState('');
    const [errors, setErrors] = useState<{ name?: string }>({});
    const [tokens, setTokens] = useState(settings.tokens);

    const reset = () => {
        setTokenName('');
        setProcessing(false);
        setErrors({});
    };

    const submit = async (e: FormEvent<Element>) => {
        e.preventDefault();
        setProcessing(true);
        setErrors({});

        try {
            const response = await window.axios.post(route('api.access-tokens.store'), { name: tokenName });
            setShowTokenDialog(false);
            reset();

            setTokens([...tokens, response.data]);
            toast.success('Access token created successfully.');
        } catch (error) {
            const err = error as AxiosError;
            if (err.response?.data && 'errors' in (err.response.data as any)) {
                setErrors((err.response.data as any).errors);
            } else {
                toast.error('Failed to create access token. Please try again.');
            }
        } finally {
            setProcessing(false);
        }
    };

    const handleCancel = (e: boolean) => {
        setShowTokenDialog(e);
        reset();
    };

    const handleDelete = async (id: number | null) => {
        if (id === null) {
            return;
        }

        try {
            await window.axios.delete(route('api.access-tokens.destroy', { access_token: id }));
            setTokens(tokens.filter((token) => token.id !== id));
            toast.success('Access token deleted successfully.');
        } catch (error) {
            toast.error('Failed to delete access token. Please try again.');
        }
    };

    return (
        <Authenticated
            breadcrumbs={[
                { name: 'Settings', icon: 'Settings', href: route('settings.index') },
                { name: 'Access Tokens' },
            ]}
            headerAction={
                <Button onClick={() => setShowTokenDialog(true)} data-dusk="button-access-token-create">
                    <PlusCircle className="mr-2 inline-block h-6 w-6" />
                    New Access Token
                </Button>
            }
        >
            <Head title="Access Tokens" />
            <div className="mx-auto w-full py-12">
                <div className="">
                    <div className="overflow-hidden">
                        <Card className="">
                            <CardContent className="px-12 py-4">
                                <Table tokens={tokens} onDelete={handleDelete} />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
            <Dialog open={showTokenDialog} onOpenChange={handleCancel}>
                <DialogContent data-dusk="dialog-access-token-create">
                    <DialogHeader>
                        <DialogTitle className="mb-4">New Access Token</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submit}>
                        <Label htmlFor="name" aria-required>
                            Name
                        </Label>
                        <Input
                            id="name"
                            name="token_name"
                            type="text"
                            value={tokenName}
                            onChange={(e) => setTokenName(e.target.value)}
                            className="w-full"
                            disabled={processing}
                            autoComplete="off"
                        />
                        {errors.name && <InputError message={errors.name} />}
                        <div className="flex items-center justify-end">
                            <DialogClose asChild>
                                <Button type="button" variant="link" className="mt-3" disabled={processing}>
                                    Cancel
                                </Button>
                            </DialogClose>
                            <Button
                                type="submit"
                                className="mt-4"
                                disabled={processing}
                                data-dusk="button-access-token-create-submit"
                            >
                                {processing ? 'Creating...' : 'Create Access Token'}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
            <Toaster richColors closeButton />
        </Authenticated>
    );
}
