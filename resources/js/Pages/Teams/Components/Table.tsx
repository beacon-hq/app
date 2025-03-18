import { User, UserCollection } from '@/Application';
import UserAvatar from '@/Components/UserAvatar';
import { DataTable } from '@/Components/ui/data-table';
import { DataTableColumnHeader } from '@/Components/ui/data-table-column-header';
import { ColumnDef, createColumnHelper } from '@tanstack/react-table';

export default function Table({ users }: { users: UserCollection }) {
    const columnHelper = createColumnHelper<User>();

    const columns: ColumnDef<User, unknown>[] = [
        columnHelper.display({
            id: 'avatar',
            header: ({ column }) => <DataTableColumnHeader column={column} title="" />,
            enableSorting: false,
            enableHiding: false,
            cell: function ({ row }) {
                return <UserAvatar user={row.original} />;
            },
        }) as ColumnDef<User>,
        columnHelper.accessor('name', {
            id: 'name',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Name" />,
            enableSorting: true,
            enableHiding: false,
        }) as ColumnDef<User>,
        columnHelper.accessor('email', {
            id: 'email',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Email" />,
            enableSorting: true,
            enableHiding: false,
        }) as ColumnDef<User>,
        columnHelper.accessor('roles', {
            id: 'Roles',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Roles" />,
            enableSorting: true,
            enableHiding: false,
            cell: ({ cell }) => cell.getValue().join(', '),
        }) as ColumnDef<User>,
        columnHelper.accessor('email_verified_at', {
            id: 'status',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Status" />,
            enableSorting: true,
            enableHiding: false,
            cell: ({ cell }) => (cell.getValue() ? 'Active' : 'Pending'),
        }) as ColumnDef<User>,
    ];

    return (
        <DataTable
            columns={columns}
            data={users}
            filter={{ placeholder: 'Filter users…', column: 'name' }}
            pageSize={20}
            columnVisibility={{ Updated: false }}
        />
    );
}
