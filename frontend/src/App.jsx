import { useEffect, useState } from 'react';
import { api } from './api';
import './App.css';

function App() {
    const [users, setUsers] = useState([]);
    const [articles, setArticles] = useState([]);
    const [userForm, setUserForm] = useState({
        username: '',
        firstName: '',
        lastName: '',
        role: 'blogger',
        password: '',
    });
    const [articleForm, setArticleForm] = useState({
        title: '',
        description: '',
    });
    const [message, setMessage] = useState('');

    useEffect(() => {
        fetchUsers();
        fetchArticles();
    }, []);

    const fetchUsers = async () => {
        try {
            const data = await api.listUsers();
            setUsers(data);
        } catch (err) {
            setMessage(err.message);
        }
    };

    const fetchArticles = async () => {
        try {
            const data = await api.listArticles();
            setArticles(data);
        } catch (err) {
            setMessage(err.message);
        }
    };

    const handleUserSubmit = async (e) => {
        e.preventDefault();
        setMessage('');
        try {
            await api.createUser(userForm);
            setUserForm({
                username: '',
                firstName: '',
                lastName: '',
                role: 'blogger',
                password: '',
            });
            fetchUsers();
        } catch (err) {
            setMessage(err.message);
        }
    };

    const handleArticleSubmit = async (e) => {
        e.preventDefault();
        setMessage('');
        try {
            await api.createArticle(articleForm);
            setArticleForm({ title: '', description: '' });
            fetchArticles();
        } catch (err) {
            setMessage(err.message);
        }
    };

    return (
        <div className="app">
            <h1>Blog Admin</h1>
            {message && <p className="error">{message}</p>}

            <section>
                <h2>Create User</h2>
                <form onSubmit={handleUserSubmit}>
                    <input
                        placeholder="Username"
                        value={userForm.username}
                        onChange={(e) => setUserForm({ ...userForm, username: e.target.value })}
                    />
                    <input
                        placeholder="First name"
                        value={userForm.firstName}
                        onChange={(e) => setUserForm({ ...userForm, firstName: e.target.value })}
                    />
                    <input
                        placeholder="Last name"
                        value={userForm.lastName}
                        onChange={(e) => setUserForm({ ...userForm, lastName: e.target.value })}
                    />
                    <select
                        value={userForm.role}
                        onChange={(e) => setUserForm({ ...userForm, role: e.target.value })}
                    >
                        <option value="blogger">Blogger</option>
                        <option value="admin">Admin</option>
                    </select>
                    <input
                        placeholder="Password"
                        type="password"
                        value={userForm.password}
                        onChange={(e) => setUserForm({ ...userForm, password: e.target.value })}
                    />
                    <button type="submit">Create User</button>
                </form>

                <h3>Users</h3>
                <ul>
                    {users.map((user) => (
                        <li key={user.id}>
                            {user.username} ({user.role})
                            <button onClick={() => api.deleteUser(user.id).then(fetchUsers)}>
                                Delete
                            </button>
                        </li>
                    ))}
                </ul>
            </section>

            <section>
                <h2>Create Article</h2>
                <form onSubmit={handleArticleSubmit}>
                    <input
                        placeholder="Title"
                        value={articleForm.title}
                        onChange={(e) =>
                            setArticleForm({ ...articleForm, title: e.target.value })
                        }
                    />
                    <textarea
                        placeholder="Description"
                        value={articleForm.description}
                        onChange={(e) =>
                            setArticleForm({ ...articleForm, description: e.target.value })
                        }
                    />
                    <button type="submit">Create Article</button>
                </form>

                <h3>Articles</h3>
                <ul>
                    {articles.map((article) => (
                        <li key={article.id}>
                            {article.title}
                            <button onClick={() => api.deleteArticle(article.id).then(fetchArticles)}>
                                Delete
                            </button>
                        </li>
                    ))}
                </ul>
            </section>
        </div>
    );
}
export default App;
