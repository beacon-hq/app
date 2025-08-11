import { AuroraText } from '@/Components/magicui/aurora-text';
import { Marquee } from '@/Components/magicui/marquee';
import { ShineBorder } from '@/Components/magicui/shine-border';
import { Avatar, AvatarImage } from '@/Components/ui/avatar';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { CardGroup, CardGroupHeader, CardGroupTitle } from '@/Components/ui/card-group';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head } from '@inertiajs/react';

export default function OurTeam() {
    return (
        <GuestLayout>
            <Head title="Meet the Team" />
            <section className="w-1/4">
                <div className="prose dark:prose-invert">
                    <h1 className="text-center">Meet the Team</h1>
                    <p>
                        Beacon is built by a small team of one, standing on the shoulders of the open source community.
                        We plan to grow in the near future.
                    </p>
                </div>
                <Card className="mt-12 group">
                    <div className="bg-secondary">
                        <Avatar className="w-1/2 h-1/2 mx-auto my-8 relative [background:linear-gradient(45deg,#fff,theme(colors.white)_50%,#fff)_padding-box,conic-gradient(from_var(--border-angle),#07e38f,#00e7aa,#13daf4,#07baf9,_theme(colors.sky.700/.48))_border-box] border-3 border-transparent motion-safe:animate-border">
                            <AvatarImage
                                src="/images/team/davey/avatar.jpg"
                                alt="Davey Shafik"
                                className="rounded grayscale group-hover:grayscale-0 group-hover:motion-preset-oscillate group-hover:motion-loop-once"
                            />
                        </Avatar>
                    </div>
                    <CardHeader className="">
                        <CardTitle className="text-4xl">Davey Shafik</CardTitle>
                        <CardDescription>Chief Engineer</CardDescription>
                    </CardHeader>
                    <CardContent className="">
                        <p className="text-justify">
                            Davey is a seasoned software engineer with over 25 years of experience in the industry. He
                            has worked on a wide range of projects, from small startups to large enterprises, and has a
                            passion for building scalable and maintainable systems.
                        </p>
                    </CardContent>
                </Card>
            </section>
            <section className="px-20 py-8 text-center">
                <h2 className="text-3xl font-bold">
                    <AuroraText colors={['#00e281', '#00e7a4', '#00f1ed', '#00b9fa', '#009aff']} speed={4}>
                        Our Experience
                    </AuroraText>
                </h2>
                <Marquee className="[--duration:40s] opacity-50 mt-12">
                    {[
                        <a href="https://akamai.com" key="akamai">
                            <img
                                className="inline-block grayscale h-20 mr-6 contrast-100 aspect-auto"
                                alt="Akamai logo"
                                src="/images/team/davey/akamai.svg"
                            />
                        </a>,
                        <a href="https://laravel.com" key="laravel">
                            <img
                                className="inline-block grayscale h-20 mr-6 contrast-100 aspect-auto"
                                alt="Laravel logo"
                                src="/images/team/davey/laravel.svg"
                            />
                        </a>,
                        <a href="https://lyft.com" key="lyft">
                            <img
                                className="inline-block grayscale h-20 mr-6 contrast-100 aspect-auto"
                                alt="Lyft logo"
                                src="/images/team/davey/lyft.svg"
                            />
                        </a>,
                        <a href="https://pestphp.com" key="pestphp">
                            <img
                                className="inline-block grayscale h-20 mr-6 contrast-100 aspect-auto"
                                alt="Pest logo"
                                src="/images/team/davey/pest.svg"
                            />
                        </a>,
                        <a href="https://php.net" key="php">
                            <img
                                className="inline-block grayscale h-20 mr-6 contrast-100 aspect-auto"
                                alt="PHP logo"
                                src="/images/team/davey/php.svg"
                            />
                        </a>,
                        <a href="https://phpunit.de" key="phpunit">
                            <img
                                className="inline-block grayscale h-20 mr-6 contrast-100 aspect-auto"
                                alt="PHPUnit logo"
                                src="/images/team/davey/phpunit.svg"
                            />
                        </a>,
                        <a href="https://www.hashicorp.com/en/products/terraform" key="terraform">
                            <img
                                className="inline-block grayscale h-20 mr-6 contrast-100 aspect-auto"
                                alt="Terraform logo"
                                src="/images/team/davey/terraform.svg"
                            />
                        </a>,
                        <a href="https://www.engineyard.com" key="engineyard">
                            <img
                                className="inline-block grayscale h-20 mr-6 contrast-100 aspect-auto"
                                alt="Engine Yard logo"
                                src="/images/team/davey/engine-yard.svg"
                            />
                        </a>,
                    ]
                        .sort((a, b) => 0.5 - Math.random())
                        .map((logo, index) => logo)}
                </Marquee>
            </section>
        </GuestLayout>
    );
}
