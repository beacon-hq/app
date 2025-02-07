import { AccessTokenCollection } from '@/Application';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Dialog, DialogClose, DialogContent, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Toaster } from '@/Components/ui/sonner';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import Table from '@/Pages/Settings/APITokens/Components/Table';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import { AxiosError, AxiosResponse } from 'axios';
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

    const submit = (e: FormEvent<Element>) => {
        e.preventDefault();
        setProcessing(true);

        window.axios
            .post(route('api.tokens.store'), { name: tokenName })
            .then((response: AxiosResponse | void) => {
                setShowTokenDialog(false);
                reset();

                setTokens([...tokens, (response as AxiosResponse).data]);
                toast.success('API Token created successfully.');
            })
            .catch((err: AxiosError) => {
                setErrors((err.response as any).data.errors);
                setProcessing(false);
            });
    };

    const handleCancel = (e: boolean) => {
        setShowTokenDialog(e);
        reset();
    };

    const handleDelete = (id: number | null) => {
        if (id === null) {
            return;
        }

        window.axios.delete(route('tokens.destroy', { token: id })).then((response) => {
            setTokens(tokens.filter((token) => token.id !== id));
            toast.success('API Token deleted successfully.');
        });
    };

    return (
        <Authenticated breadcrumbs={[{ name: 'Settings' }, { name: 'API Tokens' }]}>
            <Head title="Settings" />
            <div className="mx-auto w-full py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden p-4 shadow-sm sm:rounded-lg">
                        <div className="flex justify-end">
                            <Button onClick={() => setShowTokenDialog(true)}>
                                <PlusCircle className="mr-2 inline-block h-6 w-6" />
                                New API Token
                            </Button>
                        </div>
                        <Card className="mt-8">
                            <CardContent className="px-12 py-4">
                                <Table tokens={tokens} onDelete={handleDelete} />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
            <Dialog open={showTokenDialog} onOpenChange={handleCancel}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle className="mb-4">New API Token</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submit}>
                        <Label htmlFor="name" aria-required>
                            Name
                        </Label>
                        <Input
                            name="token_name"
                            type="text"
                            value={tokenName}
                            onChange={(e) => setTokenName(e.target.value)}
                            className="w-full"
                        />
                        {errors.name && <InputError message={errors.name} />}
                        <div className="flex items-center justify-end">
                            <DialogClose asChild>
                                <Button type="button" variant="link" className="mt-3">
                                    Cancel
                                </Button>
                            </DialogClose>
                            <Button type="submit" className="mt-4" disabled={processing}>
                                Create API Token
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
            <Toaster richColors closeButton />
        </Authenticated>
    );
}
